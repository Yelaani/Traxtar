<?php

class UserHelper
{
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    
    public static function isModernHash(string $stored): bool {
        return str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2');
    }

    
    public static function verifyPassword(string $password, string $stored): bool {
        if (self::isModernHash($stored)) {
            return password_verify($password, $stored);
        }
        if (ctype_xdigit($stored) && strlen($stored) === 32) {
            return hash_equals($stored, md5($password));   
        }
        if (ctype_xdigit($stored) && strlen($stored) === 40) {
            return hash_equals($stored, sha1($password)); 
        }
        return hash_equals($stored, $password);          
    }

    
    public static function upgradeIfNeeded(string $password, string $stored): ?string {
        if (!self::isModernHash($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            return self::hashPassword($password);
        }
        return null;
    }
}
