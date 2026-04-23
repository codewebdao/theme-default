<?php include '_header.php'; ?>
<div style="padding: 20px; font-size: 16px; line-height: 1.5;">
    <h3><?php _e('New Notification') ?></h3>
    <p><?php _e('Hi') ?> <strong><?php echo $username; ?></strong>,</p>
    <p><?php _e('You have a new notification on your %1% account:', option('site_brand')) ?></p>
    
    <div style="margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; border-radius: 8px;">
        <p style="margin: 0 0 15px 0; font-weight: bold; color: #333;"><?php _e('Notification Details:') ?></p>
        <div style="background-color: #fff; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; margin: 10px 0;">
            <p style="margin: 0; font-size: 16px; color: #333; line-height: 1.5;"><?php echo $message; ?></p>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo admin_url('/'); ?>" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #17a2b8, #138496); color: #fff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 15px rgba(23,162,184,0.3);"><?php _e('View Details') ?></a>
    </div>
    
    <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #0c5460;"><strong><?php _e('Note:') ?></strong> <?php _e('Log in to your account to view more details and manage your notifications.') ?></p>
    </div>
</div>
<?php include '_footer.php'; ?>