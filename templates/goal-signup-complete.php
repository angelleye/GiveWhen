<?php
/**
 * GiveWhen Thankyou template.
 *
 * This template can be overriden by copying this file to your-theme/GiveWhen/goal-signup-complete.php
 *
 * @author 	Angell EYE <andrew@angelleye.com>
 * @package 	Givewhen
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
?>

<?php 
get_header();
?>

<div class="gw_center_container">
    <div class="gwcontainer">
        <div class="gw_heading gw_heading-center">            
<?php
if(isset($_REQUEST['goal']) && isset($_REQUEST['amt'])){
    $the_slug = $_REQUEST['goal'];
    $amount = base64_decode($_REQUEST['amt']);
    $args = array(
      'name'        => $the_slug,
      'post_type'   => 'give_when_goals',
      'post_status' => 'publish',
      'numberposts' => 1
    );
    $my_posts = get_posts($args);
    if( $my_posts ) {
        $user_id = base64_decode($_REQUEST['user']);
        $user = get_user_by('id', $user_id);        
        $post_id = $my_posts[0]->ID;
        $post_meta_array = get_post_meta($post_id);
        $trigger_name = $post_meta_array['trigger_name'][0];
        $trigger_thing = $post_meta_array['trigger_thing'][0];
        $ccode = get_option('gw_currency_code');
        $paypal = new Give_When_PayPal_Helper();
        $symbol = $paypal->get_currency_symbol($ccode);
        echo "<h2>".__('Hi '). $user->display_name . __(', Thank You for signed up in ') . $trigger_name. "</h2>";
        echo "<span>" . __('Each time you will give ').$symbol.$amount.' '. __('when').' '.$trigger_thing. "</span>";
        $EmailString='';
        $EmailString.='<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                                <tr>
                                    <td align="center" valign="top">
                                        <div id="template_header_image">
                                        </div>
                                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                                            <tr>
                                                <td align="center" valign="top">                        
                                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                                        <tr>
                                                            <td valign="top" id="body_content">                                    
                                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td valign="top">
                                                                            <div id="body_content_inner">
                                                                                <h2 style="text-align:center;color: #008CBA;">Hi '.$current_user->display_name.',Thank You for signed up in '.$trigger_name.'</h2>
                                                                                <p style="font-size: 16px;text-align: center;font-family: inherit; color: rgb(255, 111, 0)"><strong>Each time you will give $ '.$amount.' when '.$trigger_thing.'</strong></p>
                                                                        </td>
                                                                    </tr>
                                                                </table>                                    
                                                            </td>
                                                        </tr>
                                                    </table>                        
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>';

        $headers = "From: noreply@givewhen.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $to = $current_user->user_email;
        $subject = 'Thank you for giving!';
        $message = $EmailString;
        wp_mail($to, $subject, $message, $headers);
    }
}
else{
    ?>
            <h3><?php echo __("You are accessing this page without signed up for GiveWhen Goal"); ?></h3>
            <span><?php echo __("Try again Sigining in for GiveWhen Goals."); ?></span>
<?php
    }
?>
         </div>
    </div>
</div>
<?php
get_footer();