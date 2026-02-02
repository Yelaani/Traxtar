<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class DownloadProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:download-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download product images from Unsplash based on product names';

    /**
     * Image mappings for each product type
     */
    protected $imageMappings = [
        // Women's products
        "Women's Seamless Leggings" => 'https://images.unsplash.com/photo-1591085686350-798c0f9faa7f?w=800&h=800&fit=crop',
        "Women's Medium-Support Sports Bra" => 'https://images.unsplash.com/photo-1594736797933-d0cbc0b0c0b0?w=800&h=800&fit=crop',
        "Women's Airflow Tank" => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=800&fit=crop',
        "Women's Sprint 3\" Shorts" => 'https://images.unsplash.com/photo-1594736797933-d0cbc0b0c0b0?w=800&h=800&fit=crop',
        "Women's Zip Hoodie" => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&h=800&fit=crop',
        "Women's Long-Sleeve Tech Tee" => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=800&fit=crop',
        "Women's Packable Windbreaker" => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&h=800&fit=crop',
        
        // Men's products
        "Men's Flex Training Shorts 7\"" => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=800&fit=crop',
        "Men's Performance Tee" => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop',
        "Men's Compression Top" => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop',
        "Men's Tapered Joggers" => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=800&fit=crop',
        "Men's Fleece Hoodie" => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&h=800&fit=crop',
        "Men's Court Polo" => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop',
        "Men's Run Tights" => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=800&fit=crop',
        
        // Accessories
        "Insulated Water Bottle 750ml" => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&h=800&fit=crop',
        "Aero Cap" => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=800&h=800&fit=crop',
        "Cushion Crew Socks 3-Pack" => 'https://images.unsplash.com/photo-1586350977773-bf8b4f3b3c3c?w=800&h=800&fit=crop',
        "Convertible Gym Bag 35L" => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=800&fit=crop',
        "Grip Yoga Mat 5mm" => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&h=800&fit=crop',
        "Sweat Wristbands 2-Pack" => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=800&fit=crop',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Downloading product images...');

        // Ensure the images directory exists
        $imagesPath = storage_path('app/public/images');
        if (!is_dir($imagesPath)) {
            mkdir($imagesPath, 0755, true);
        }

        $products = Product::all();
        $downloaded = 0;
        $failed = 0;

        foreach ($products as $product) {
            $imageUrl = $this->getImageUrlForProduct($product->name);
            
            if (!$imageUrl) {
                $this->warn("No image URL found for: {$product->name}");
                $failed++;
                continue;
            }

            try {
                // Get the image filename from the product's image path
                $filename = basename($product->image ?? 'product_' . $product->id . '.jpg');
                
                // If product doesn't have an image path, create one
                if (!$product->image) {
                    $filename = 'product_' . $product->id . '_' . time() . '.jpg';
                }

                $filePath = 'images/' . $filename;

                // Download the image
                $response = Http::timeout(30)->get($imageUrl);
                
                if ($response->successful()) {
                    // Save to storage
                    Storage::disk('public')->put($filePath, $response->body());
                    
                    // Update product with image path
                    $product->update(['image' => $filePath]);
                    
                    $this->info("✓ Downloaded image for: {$product->name}");
                    $downloaded++;
                } else {
                    $this->error("✗ Failed to download image for: {$product->name}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Error downloading image for {$product->name}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("\nCompleted! Downloaded: {$downloaded}, Failed: {$failed}");
    }

    /**
     * Get the Unsplash image URL for a product
     */
    protected function getImageUrlForProduct(string $productName): ?string
    {
        // Check exact match first
        if (isset($this->imageMappings[$productName])) {
            return $this->imageMappings[$productName];
        }

        // Try to match by keywords
        $name = strtolower($productName);
        
        if (str_contains($name, 'leggings')) {
            return 'https://images.unsplash.com/photo-1591085686350-798c0f9faa7f?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'sports bra') || str_contains($name, 'bra')) {
            return 'https://images.unsplash.com/photo-1594736797933-d0cbc0b0c0b0?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'tank') || str_contains($name, 'tee') || str_contains($name, 'top')) {
            return 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'shorts')) {
            return 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'hoodie') || str_contains($name, 'windbreaker') || str_contains($name, 'jacket')) {
            return 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'joggers') || str_contains($name, 'tights')) {
            return 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'polo') || str_contains($name, 'shirt')) {
            return 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'bottle')) {
            return 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'cap') || str_contains($name, 'hat')) {
            return 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'socks')) {
            return 'https://images.unsplash.com/photo-1586350977773-bf8b4f3b3c3c?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'bag') || str_contains($name, 'backpack')) {
            return 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'yoga mat') || str_contains($name, 'mat')) {
            return 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&h=800&fit=crop';
        } elseif (str_contains($name, 'wristband')) {
            return 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=800&fit=crop';
        }

        return null;
    }
}
