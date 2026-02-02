<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MatchProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:match-images {--update-seeder : Update ProductSeeder.php with matched images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match product images to products by normalizing names and filenames';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Matching product images to products...');
        $this->newLine();

        // Get all image files
        $imagesPath = storage_path('app/public/images');
        if (!is_dir($imagesPath)) {
            $this->error("Images directory not found: {$imagesPath}");
            return 1;
        }

        $imageFiles = File::files($imagesPath);
        $imageFiles = array_filter($imageFiles, function ($file) {
            $ext = strtolower($file->getExtension());
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });

        $this->info("Found " . count($imageFiles) . " image files");
        $this->newLine();

        // Get all products
        $products = Product::all();
        $this->info("Found " . $products->count() . " products");
        $this->newLine();

        // Create normalized mappings
        $normalizedImages = [];
        foreach ($imageFiles as $file) {
            $normalized = $this->normalizeForMatching($file->getFilename());
            $normalizedImages[$normalized] = [
                'filename' => $file->getFilename(),
                'path' => 'images/' . $file->getFilename(),
            ];
        }

        // Match products to images
        $matches = [];
        $unmatchedProducts = [];
        $unmatchedImages = array_keys($normalizedImages);

        foreach ($products as $product) {
            $normalizedProductName = $this->normalizeForMatching($product->name);
            
            // Try exact match first
            if (isset($normalizedImages[$normalizedProductName])) {
                $matches[$product->id] = [
                    'product' => $product,
                    'image' => $normalizedImages[$normalizedProductName],
                ];
                unset($unmatchedImages[array_search($normalizedProductName, $unmatchedImages)]);
            } else {
                // Try fuzzy match
                $bestMatch = $this->findBestMatch($normalizedProductName, $normalizedImages);
                if ($bestMatch) {
                    $matches[$product->id] = [
                        'product' => $product,
                        'image' => $bestMatch,
                    ];
                    $normalizedMatch = $this->normalizeForMatching($bestMatch['filename']);
                    unset($unmatchedImages[array_search($normalizedMatch, $unmatchedImages)]);
                } else {
                    $unmatchedProducts[] = $product;
                }
            }
        }

        // Display results
        $this->info('=== MATCHING RESULTS ===');
        $this->newLine();

        if (count($matches) > 0) {
            $this->info("✓ Matched " . count($matches) . " products:");
            $this->newLine();
            
            $tableData = [];
            foreach ($matches as $match) {
                $tableData[] = [
                    'Product' => $match['product']->name,
                    'Image' => $match['image']['filename'],
                ];
            }
            $this->table(['Product', 'Image'], $tableData);
            $this->newLine();
        }

        if (count($unmatchedProducts) > 0) {
            $this->warn("⚠ " . count($unmatchedProducts) . " products without matching images:");
            foreach ($unmatchedProducts as $product) {
                $this->line("  - {$product->name}");
            }
            $this->newLine();
        }

        if (count($unmatchedImages) > 0) {
            $this->warn("⚠ " . count($unmatchedImages) . " images without matching products:");
            foreach ($unmatchedImages as $normalized) {
                $filename = $normalizedImages[$normalized]['filename'];
                $this->line("  - {$filename}");
            }
            $this->newLine();
        }

        // Update ProductSeeder if requested
        if ($this->option('update-seeder') && count($matches) > 0) {
            $this->updateProductSeeder($matches);
        } elseif (!$this->option('update-seeder')) {
            $this->info('Tip: Use --update-seeder flag to automatically update ProductSeeder.php');
        }

        return 0;
    }

    /**
     * Normalize text for matching
     */
    protected function normalizeForMatching(string $text): string
    {
        // Remove file extension if present
        $text = pathinfo($text, PATHINFO_FILENAME);
        
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove quotes
        $text = str_replace(["'", '"'], '', $text);
        
        // Replace special characters with spaces
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        return $text;
    }

    /**
     * Find best matching image for a product name
     */
    protected function findBestMatch(string $normalizedProductName, array $normalizedImages): ?array
    {
        $bestMatch = null;
        $bestScore = 0;

        foreach ($normalizedImages as $normalizedImage => $imageData) {
            // Calculate similarity using Levenshtein distance
            $distance = levenshtein($normalizedProductName, $normalizedImage);
            $maxLength = max(strlen($normalizedProductName), strlen($normalizedImage));
            $similarity = $maxLength > 0 ? (1 - ($distance / $maxLength)) : 0;

            // Also check if product name contains image name or vice versa
            if (str_contains($normalizedProductName, $normalizedImage) || 
                str_contains($normalizedImage, $normalizedProductName)) {
                $similarity += 0.3; // Boost for substring matches
            }

            if ($similarity > $bestScore && $similarity > 0.7) { // Minimum 70% similarity
                $bestScore = $similarity;
                $bestMatch = $imageData;
            }
        }

        return $bestMatch;
    }

    /**
     * Update ProductSeeder with matched images
     */
    protected function updateProductSeeder(array $matches): void
    {
        $seederPath = database_path('seeders/ProductSeeder.php');
        
        if (!File::exists($seederPath)) {
            $this->error("ProductSeeder.php not found at: {$seederPath}");
            return;
        }

        $content = File::get($seederPath);
        
        // Create a mapping of product names to image paths
        $productImageMap = [];
        foreach ($matches as $match) {
            $productName = $match['product']->name;
            $imagePath = $match['image']['path'];
            $productImageMap[$productName] = $imagePath;
        }

        // Update each product entry in the seeder
        // We'll process line by line to handle quotes properly
        $lines = explode("\n", $content);
        $updatedLines = [];
        $currentProductName = null;
        $inProduct = false;
        
        foreach ($lines as $line) {
            // Check if this line contains a product name
            foreach ($productImageMap as $productName => $imagePath) {
                // Escape single quotes in product name for matching
                $escapedProductName = str_replace("'", "\\'", $productName);
                
                // Check if this line contains the product name
                if (preg_match("/['\"]name['\"]\s*=>\s*['\"]" . preg_quote($escapedProductName, '/') . "['\"]/", $line)) {
                    $currentProductName = $productName;
                    $inProduct = true;
                    $updatedLines[] = $line;
                    continue 2; // Continue outer loop
                }
            }
            
            // If we're in a product and find the image line, update it
            if ($inProduct && $currentProductName && preg_match("/['\"]image['\"]\s*=>/", $line)) {
                $imagePath = $productImageMap[$currentProductName];
                // Escape single quotes in image path
                $escapedImagePath = str_replace("'", "\\'", $imagePath);
                $updatedLines[] = "                'image' => '{$escapedImagePath}',";
                $inProduct = false;
                $currentProductName = null;
                continue;
            }
            
            // If we hit the closing bracket of the product array, reset
            if ($inProduct && strpos($line, '],') !== false) {
                $inProduct = false;
                $currentProductName = null;
            }
            
            $updatedLines[] = $line;
        }
        
        $content = implode("\n", $updatedLines);

        // Write updated content
        File::put($seederPath, $content);
        
        $this->info("✓ Updated ProductSeeder.php with matched image paths");
    }
}
