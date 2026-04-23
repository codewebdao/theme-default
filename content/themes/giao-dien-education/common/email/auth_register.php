<?php include '_header.php'; ?>
<div style="padding: 20px; font-size: 16px; line-height: 1.5;">
    <h3><?php _e('New Register Activation Code') ?></h3>
    <p><?php _e('Hi') ?> <strong><?php echo $username; ?></strong>,</p>
    <p><?php _e('You have requested a new Register Activation Code for your account. Please use the verification code below to complete your account activation:') ?></p>
    
    <div style="margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
        <p style="margin: 0 0 15px 0; font-weight: bold; color: #333;"><?php _e('Your New 8-Digit Verification Code:') ?></p>
        <div style="text-align: center; margin: 20px 0;">
            <span style="display: inline-block; padding: 20px 30px; background-color: #fff; border: 3px solid #28a745; border-radius: 10px; font-size: 28px; font-weight: bold; letter-spacing: 4px; color: #28a745; font-family: 'Courier New', monospace; box-shadow: 0 2px 10px rgba(40,167,69,0.2);"><?php echo $activation_code; ?></span>
        </div>
        <p style="margin: 15px 0 0 0; font-size: 14px; color: #666; text-align: center;"><?php _e('Enter this code on the confirmation page to activate your account.') ?></p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo $activation_link; ?>" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #28a745, #1e7e34); color: #fff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(40,167,69,0.3);"><?php _e('Activate Account') ?></a>
    </div>
    
    <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #155724;"><strong><?php _e('Note:') ?></strong> <?php _e('This new verification code will expire in 24 hours. The previous code is no longer valid.') ?></p>
    </div>
    
    <p style="font-size: 14px; color: #666;"><?php _e('If you did not request a new activation code, please contact our support team immediately.') ?></p>
</div>
<?php include '_footer.php'; ?>
