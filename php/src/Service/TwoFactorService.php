<?php
/**
 * TwoFactorService - 2FA Authentication Service
 * Uses pragmarx/google2fa for TOTP generation/validation
 * Uses bacon/bacon-qr-code for QR code generation
 */

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorService {
    private $google2fa;
    private $repository;
    private $companyName = 'BuzzDM';

    public function __construct() {
        $this->google2fa = new Google2FA();
        $this->repository = new UserRepository();
    }

    /**
     * Generate a new secret key for the user
     * @return string 32-character base32 encoded secret
     */
    public function generateSecret(): string {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Generate QR code URL for Google Authenticator
     * @param string $email User's email
     * @param string $secret Secret key
     * @return string otpauth:// URL
     */
    public function getQRCodeUrl(string $email, string $secret): string {
        return $this->google2fa->getQRCodeUrl(
            $this->companyName,
            $email,
            $secret
        );
    }

    /**
     * Generate QR code as SVG string
     * @param string $email User's email
     * @param string $secret Secret key
     * @return string SVG markup
     */
    public function getQRCodeSvg(string $email, string $secret): string {
        $qrCodeUrl = $this->getQRCodeUrl($email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify a TOTP code
     * @param string $secret User's secret key
     * @param string $code 6-digit code from authenticator app
     * @param int $window Time window for code validity (default: 2 = 60 seconds before/after)
     * @return bool Whether code is valid
     */
    public function verifyCode(string $secret, string $code, int $window = 2): bool {
        return $this->google2fa->verifyKey($secret, $code, $window);
    }

    /**
     * Enable 2FA for a user (after code verification)
     * @param int $userId User ID
     * @param string $secret Secret key to store
     * @param string $code Verification code to confirm
     * @return bool Success
     */
    public function enable(int $userId, string $secret, string $code): bool {
        // Verify the code first
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        return $this->repository->enable2FA($userId, $secret);
    }

    /**
     * Disable 2FA for a user
     * @param int $userId User ID
     * @return bool Success
     */
    public function disable(int $userId): bool {
        return $this->repository->disable2FA($userId);
    }

    /**
     * Check if user has 2FA enabled
     * @param int $userId User ID
     * @return bool Whether 2FA is enabled
     */
    public function isEnabled(int $userId): bool {
        $user = $this->repository->findById($userId);
        return $user && isset($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1;
    }

    /**
     * Get user's 2FA secret
     * @param int $userId User ID
     * @return string|null Secret key or null if not set
     */
    public function getSecret(int $userId): ?string {
        return $this->repository->get2FASecret($userId);
    }

    /**
     * Validate 2FA for login
     * @param int $userId User ID
     * @param string $code 6-digit TOTP code
     * @return bool Whether login should proceed
     */
    public function validateLogin(int $userId, string $code): bool {
        $secret = $this->getSecret($userId);

        if (!$secret) {
            return false;
        }

        return $this->verifyCode($secret, $code);
    }
}
