<?php include '_header.php'; ?>
<div style="padding: 20px; font-size: 16px; line-height: 1.5;">
    <h3><?php _e('New Password Reset Code') ?></h3>
    <p><?php _e('Hi') ?> <strong><?php echo $username; ?></strong>,</p>
    <p><?php _e('You have requested a new password reset code for your account. Please use the verification code below to proceed with password reset:') ?></p>
    
    <div style="margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #ffc107; border-radius: 8px;">
        <p style="margin: 0 0 15px 0; font-weight: bold; color: #333;"><?php _e('Your New 8-Digit Verification Code:') ?></p>
        <div style="text-align: center; margin: 20px 0;">
            <span style="display: inline-block; padding: 20px 30px; background-color: #fff; border: 3px solid #ffc107; border-radius: 10px; font-size: 28px; font-weight: bold; letter-spacing: 4px; color: #ffc107; font-family: 'Courier New', monospace; box-shadow: 0 2px 10px rgba(255,193,7,0.2);"><?php echo $reset_code; ?></span>
        </div>
        <p style="margin: 15px 0 0 0; font-size: 14px; color: #666; text-align: center;"><?php _e('Enter this code on the confirmation page to proceed with password reset.') ?></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo $reset_link; ?>" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #ffc107, #e0a800); color: #000; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(255,193,7,0.3);"><?php _e('Reset Password') ?></a>
    </div>
    
    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #856404;"><strong><?php _e('Important:') ?></strong> <?php _e('This new verification code will expire in 24 hours. The previous code is no longer valid.') ?></p>
    </div>
    
    <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #0c5460;"><strong><?php _e('Security:') ?></strong> <?php _e('If you did not request a new password reset code, please contact our support team immediately and consider changing your password.') ?></p>
    </div>
</div>
<?php include '_footer.php'; ?>
