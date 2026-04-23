<?php include '_header.php'; ?>
<div style="padding: 20px; font-size: 16px; line-height: 1.5;">
    <h3><?php _e('Welcome to %1%!', option('site_brand')) ?></h3>
    <p><?php _e('Hi') ?> <strong><?php echo $username; ?></strong>,</p>
    <p><?php _e('Thank you for registering with %1%! Your account has been created successfully and is now active.', option('site_brand')) ?></p>
    
    <div style="margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #28a745; border-radius: 8px;">
        <p style="margin: 0 0 15px 0; font-weight: bold; color: #333;"><?php _e('Account Status:') ?></p>
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 15px; margin: 10px 0;">
            <p style="margin: 0; font-size: 16px; color: #155724; font-weight: bold;">🎉 <?php _e('Congratulations! Your account is now ready to use.') ?></p>
        </div>
    </div>
    
    <p><?php _e('You can now log in to your account and start using our services:') ?></p>
    <div style="background-color: #fff; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 15px 0;">
        <ul style="margin: 0; padding-left: 20px; color: #333;">
            <li style="margin-bottom: 8px;"><?php _e('Access to exclusive content') ?></li>
            <li style="margin-bottom: 8px;"><?php _e('Personalized news feed') ?></li>
            <li style="margin-bottom: 8px;"><?php _e('Comment and interact with articles') ?></li>
            <li style="margin-bottom: 8px;"><?php _e('Save your favorite articles') ?></li>
        </ul>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo auth_url('login'); ?>" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #28a745, #1e7e34); color: #fff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(40,167,69,0.3);"><?php _e('Login to Your Account') ?></a>
    </div>
    
    <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #0c5460;"><strong><?php _e('Support:') ?></strong> <?php _e('If you have any questions, please contact our support team.') ?></p>
    </div>
</div>
<?php include '_footer.php'; ?>