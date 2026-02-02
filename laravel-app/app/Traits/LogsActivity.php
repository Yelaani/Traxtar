<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an activity to the audit log.
     *
     * @param string $action
     * @param Model|null $model
     * @param string $type
     * @param string $severity
     * @param string|null $description
     * @param array|null $metadata
     * @return AuditLog
     */
    protected function logActivity(
        string $action,
        ?Model $model = null,
        string $type = 'Product Management',
        string $severity = 'info',
        ?string $description = null,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'severity' => $severity,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata ?? [],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log product creation.
     *
     * @param Model $product
     * @return AuditLog
     */
    protected function logProductCreated(Model $product): AuditLog
    {
        return $this->logActivity(
            'product.created',
            $product,
            'Product Management',
            'info',
            "Product '{$product->name}' was created",
            [
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'status' => $product->status,
            ]
        );
    }

    /**
     * Log product update with before/after values.
     *
     * @param Model $product
     * @param array $oldValues
     * @param array $newValues
     * @return AuditLog
     */
    protected function logProductUpdated(Model $product, array $oldValues, array $newValues): AuditLog
    {
        $changes = [];
        $hasPriceChange = isset($oldValues['price']) && $oldValues['price'] != $newValues['price'];
        $hasStockChange = isset($oldValues['stock']) && $oldValues['stock'] != $newValues['stock'];

        if ($hasPriceChange) {
            $changes['price'] = [
                'old' => $oldValues['price'],
                'new' => $newValues['price'],
            ];
        }

        if ($hasStockChange) {
            $changes['stock'] = [
                'old' => $oldValues['stock'],
                'new' => $newValues['stock'],
            ];
        }

        // Track other changes
        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value && !in_array($key, ['price', 'stock'])) {
                $changes[$key] = [
                    'old' => $oldValues[$key],
                    'new' => $value,
                ];
            }
        }

        return $this->logActivity(
            'product.updated',
            $product,
            'Product Management',
            'info',
            "Product '{$product->name}' was updated",
            [
                'changes' => $changes,
                'product_id' => $product->id,
            ]
        );
    }

    /**
     * Log product deletion (soft delete).
     *
     * @param Model $product
     * @return AuditLog
     */
    protected function logProductDeleted(Model $product): AuditLog
    {
        return $this->logActivity(
            'product.deleted',
            $product,
            'Product Management',
            'warning',
            "Product '{$product->name}' was deleted",
            [
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'status' => $product->status,
            ]
        );
    }
}
