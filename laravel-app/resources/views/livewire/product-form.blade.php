<div>
    <div class="max-w-lg card p-6">
        <h2 class="text-xl font-bold mb-4">{{ $productId ? 'Edit Product' : 'Create Product' }}</h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="label">Name</label>
                <input class="input" wire:model="name" type="text" required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">SKU (Optional)</label>
                <input class="input" wire:model="sku" type="text">
                @error('sku')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Price (LKR)</label>
                <input class="input" wire:model="price" type="number" step="0.01" min="0" required>
                @error('price')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Stock</label>
                <input class="input" wire:model="stock" type="number" min="0" required>
                @error('stock')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Status</label>
                <select class="input" wire:model="status" required>
                    <option value="active">Active</option>
                    <option value="hidden">Hidden</option>
                </select>
                @error('status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Description</label>
                <textarea class="input" wire:model="description" rows="3"></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if($productId && $existingImage)
                <div>
                    <label class="label">Current Image</label>
                    <img src="{{ asset('storage/' . $existingImage) }}" alt="Current image" class="w-32 h-32 object-cover mb-2 rounded">
                </div>
            @endif

            <div>
                <label class="label">{{ $productId ? 'New Image (leave empty to keep current)' : 'Image' }}</label>
                <input class="input" wire:model="image" type="file" accept="image/*">
                @error('image')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($image)
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-32 h-32 object-cover rounded">
                    </div>
                @endif
            </div>

            <div class="flex gap-2">
                <button class="btn-primary" type="submit">{{ $productId ? 'Update' : 'Save' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
