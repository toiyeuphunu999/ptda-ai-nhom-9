ALTER TABLE users
    ADD COLUMN email_verified_at DATETIME NULL AFTER email,
    ADD COLUMN email_verification_token_hash CHAR(64) NULL AFTER email_verified_at,
    ADD COLUMN email_verification_expires_at DATETIME NULL AFTER email_verification_token_hash;

-- Existing accounts are trusted so the new feature does not lock current users out.
UPDATE users
SET email_verified_at = COALESCE(email_verified_at, NOW())
WHERE email_verified_at IS NULL;

CREATE INDEX idx_users_email_verification_token
    ON users(email_verification_token_hash);
