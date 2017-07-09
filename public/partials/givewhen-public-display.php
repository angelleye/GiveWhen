<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.angelleye.com/
 * @since      1.0.0
 *
 * @package    Givewhen
 * @subpackage Givewhen/public/partials
 */
class AngellEYE_Give_When_Public_Display {

    public static function init() {
        add_shortcode('give_when_goal', array(__CLASS__, 'give_when_create_shortcode'));
        add_action( 'wp_enqueue_scripts', array(__CLASS__,'give_when_detect_shortcode'));
        add_action( 'wp_ajax_start_express_checkout', array(__CLASS__,'start_express_checkout'));
        add_action("wp_ajax_nopriv_start_express_checkout",  array(__CLASS__,'start_express_checkout'));
    }

    public static function give_when_detect_shortcode()
    {
        global $post;
        $pattern = get_shortcode_regex();

        if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
            && array_key_exists( 2, $matches )
            && in_array( 'give_when_goal', $matches[2] ) )
        {            
            wp_enqueue_style( 'givewhen-one', GW_PLUGIN_URL . 'includes/css/bootstrap/css/bootstrap.css', array(), '1.0.0','all' );
        }
    }
    
    /**
     * give_when_create_shortcode function is for generate
     * @since 1.0.0
     * @access public
     */
    public static function give_when_create_shortcode($atts, $content = null) {

        global $post, $post_ID; 
        $give_when_page_id = $post->ID;
        extract(shortcode_atts(array(
                    'id' => ''), $atts));
        $html = '';
        
        if( !empty($id) ) {
            $post = get_post($id);
            if(!empty($post->post_type) && $post->post_type == 'give_when_goals' && $post->post_status == 'publish') {
        ?>
                <div id="overlay" style=" background: #f6f6f6;opacity: 0.8;width: 100%;float: left;height: 100%;position: fixed;top: 0;left:0;right:0;z-index: 1031;text-align: center; display: none;">
                    <div style="display: table; width:100%; height: 100%;">
                        <div style="display: table-cell;vertical-align: middle;"><img src="<?php echo GW_PLUGIN_URL; ?>admin/images/loading.gif"  style=" position: relative;top: 50%; height: 100px"/>
                            <h1 style="font-weight: 600;">Please dont't go back , We are redirecting you to PayPal</h1></div>
                    </div>            
                </div>
                <div class="give_when_container">
                    <div class="row">                                               
                        <div class="col-md-12"><h1><?php echo get_post_meta( $post->ID, 'trigger_name', true ); ?></h1></div>
                        <div class="col-md-12">
                            <img src="<?php echo get_post_meta( $post->ID, 'image_url', true ) ?>">
                            <br><br>
                            <p> <?php echo get_post_meta( $post->ID, 'trigger_desc', true ); ?></p>
                            <?php echo $post->post_content; ?>
                        </div>
                        <div class="col-md-12">
                            <?php 
                                $amount = get_post_meta($post->ID,'amount',true);
                                if($amount == 'fixed'){
                                    $fixed_amount = get_post_meta($post->ID,'fixed_amount_input',true);
                                    ?>
                                <p class="lead">I will Give : $ <span id="give_when_fixed_price_span"><?php echo $fixed_amount; ?></span> When <?php echo get_post_meta( $post->ID, 'trigger_thing', true ); ?></p>
                                <?php    
                                }
                                elseif($amount == 'manual'){
                                    ?>
                                <p class="lead">I will Give : $ <span id="give_when_manual_price_span">50</span> When <?php echo get_post_meta( $post->ID, 'trigger_thing', true ); ?></p>
                                <div class="form-group">
                                     <label for="manualamout" class="control-label">Enter Amount</label>
                                    <input type="text" name="gw_manual_amount_input" value="50" class="form-control" autocomplete="off" id="gw_manual_amount_input" placeholder="Enter Amount"/>
                                </div>
                                    
                                    <?php
                                }
                                else{
                                    $option_name = get_post_meta($post->ID,'option_name',true);
                                    $option_amount = get_post_meta($post->ID,'option_amount',true);
                                    $i=0;
                            ?>
                            <p class="lead">I will Give : $ <span id="give_when_fixed_price_span_select"><?php echo $option_amount[0]; ?></span> When <?php echo get_post_meta( $post->ID, 'trigger_name', true ); ?></p>
                            <div class="form-group">
                                <select class="form-control" name="give_when_option_amount" id="give_when_option_amount">
                                <?php
                                    foreach ($option_name as $name) {
                                        echo '<option value="'.$option_amount[$i].'">'.$name.$option_amount[$i].'</option>';
                                        $i++;
                                    }
                                ?>
                                </select>
                            </div>
                            <?php } ?>
                        </div>                       
                    </div>
                                       
                     <div class="row" id="give_when_signup_form">
                        <div class="col-md-12">
                            <div class="panel panel-info">
                                <div class="panel-heading"> Sign up for <?php echo get_post_meta( $post->ID, 'trigger_name', true ); ?></div>
                                <div class="panel-body">
                                     <div class="alert alert-warning" id="connect_paypal_error_public" style="display: none">
                                        <span id="connect_paypal_error_p"></span>
                                    </div>
                                    <?php
                                     if ( is_user_logged_in() ) {
                                        $current_user    = wp_get_current_user();
                                        $User_email      = !empty($current_user->user_email) ? $current_user->user_email : '';
                                        $User_first_name = !empty($current_user->user_firstname) ? $current_user->user_firstname : '';
                                        $User_last_name  = !empty($current_user->user_lastname) ? $current_user->user_lastname : '';
                                     }
                                     else{
                                        $User_email      = '';
                                        $User_first_name = '';
                                        $User_last_name  = '';
                                     }
                                    ?>
                                    <form method="post" name="signup" id="give_when_signup">
                                        <div class="form-group">
                                          <label for="name">First Name</label>
                                          <input type="text" class="form-control" name="give_when_firstname" id="give_when_firstname" required="required" value="<?php echo $User_first_name; ?>">
                                        </div>
                                        <div class="form-group">
                                          <label for="name">Last Name</label>
                                          <input type="text" class="form-control" name="give_when_lastname" id="give_when_lastname" required="required" value="<?php echo $User_last_name; ?>">
                                        </div>
                                        <div class="form-group">
                                          <label for="email">Email address:</label>
                                          <input type="email" class="form-control" name="give_when_email" id="give_when_email" required="required" value="<?php echo $User_email; ?>">
                                        </div>
                                        <?php 
                                         if ( ! is_user_logged_in() ) {
                                         ?>
                                        <div class="form-group">
                                          <label for="password">Password:</label>
                                          <input type="password" class="form-control" name="give_when_password" id="give_when_password" required="required">
                                        </div>
                                        <div class="form-group">
                                          <label for="password">Re-type Password:</label>
                                          <input type="password" class="form-control" name="give_when_retype_password" id="give_when_retype_password" required="required">
                                        </div>
                                         <?php } ?>
                                        <input type="hidden" class="form-control" name="give_when_page_id" id="give_when_page_id" value="<?php echo $give_when_page_id;?>">
                                        <button type="button" class="btn btn-primary" id="give_when_angelleye_checkout" data-postid="<?php echo $post->ID; ?>" data-userid="">Sign Up For <?php echo get_post_meta( $post->ID, 'trigger_name', true ); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>                                        
                </div>
            <?php
            }
        }
    }
         
    public function start_express_checkout(){
        global $post;         
        $page_id = $_POST['give_when_page_id'];
        $post_id = $_POST['post_id'];
        $amount = $_POST['amount'];        
        $post = get_post($post_id);
        $cancel_page = site_url('?action=ec_cancel');
        if(!empty($_POST['formData'])){
            $gwuser = array();
            parse_str($_POST['formData'], $gwuser);
            $page_id = $gwuser['give_when_page_id'];
            $cancel_page =  get_permalink( $page_id );                    
            $role = get_role( 'giver' );
            if($role==NULL){
                add_role('giver','Giver');
            }
            $ValidationErrors = array();
            $fname = sanitize_text_field( $gwuser['give_when_firstname']);
            if (!preg_match("/^[a-zA-Z]+$/",$fname)) {
              $ValidationErrors['FirstName'] = "Invalid Input : Only letters allowed in First Name";
            }
            $lname = sanitize_text_field($gwuser['give_when_lastname']);
            if (!preg_match("/^[a-zA-Z]+$/",$lname)) {
              $ValidationErrors['LastName'] = "Invalid Input : Only letters allowed in Last Name";
            }
             
            $email = sanitize_email($gwuser['give_when_email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $ValidationErrors['Email'] = "Invalid email format";
            }
            if(!empty($ValidationErrors)){
                echo json_encode(array('Ack'=>'ValidationError','ErrorCode'=>'Invalid Inputs','ErrorLong'=>'Please find Following Error','Errors'=>$ValidationErrors));
                exit;
            }            
            
            $userdata=array(
                'user_pass' => md5($gwuser['give_when_password']),
                'user_login' => $gwuser['give_when_email'],
                'user_email' => $gwuser['give_when_email'],
                'display_name' => $gwuser['give_when_firstname'].' '.$gwuser['give_when_lastname'],
                'first_name' => $gwuser['give_when_firstname'],
                'last_name' => $gwuser['give_when_lastname'],
                'role' => 'giver'
            );
            $user_exist = email_exists($gwuser['give_when_email']);
            
            if($user_exist){
                $is_admin = user_can($user_exist, 'manage_options' );
                if($is_admin){
                    unset($userdata['role']);
                }
                
                $userdata['ID'] = $user_exist;
                $signnedup_goals = get_user_meta($user_exist,'give_when_signedup_goals');        
                $goalArray = explode('|', $signnedup_goals[0]);                
                if(!empty($goalArray)){
                    if(in_array($post_id, $goalArray)){
                        echo json_encode(array('Ack'=>'Information','ErrorCode'=>'GiveWhenInfo','ErrorShort'=>'You are already signed up for this goal.','ErrorLong'=>'You are already signed up for this goal.'));
                        exit;
                    }
                }
            }
            
            $user_id = wp_insert_user($userdata);
            if( is_wp_error( $user_id ) ) {
                $error = 'Error on user creation: ' . $user_id->get_error_message();
                echo json_encode(array('Ack'=>'Failure','ErrorCode'=>'WP Error','ErrorShort'=>'Error on user creation:','ErrorLong'=>$error));
                exit;
            }
            else{                
                    //$subject='Thanks For Joining';
                    //$message='';
                    //wp_mail($userdata['user_email'], $subject, $message);                
                wp_set_auth_cookie( $user_id, true );
            }
        }
        else{
            $user_id = $_POST['login_user_id'];
        }   
        
        $trigger_name = get_post_meta( $post->ID, 'trigger_name', true );
                        
        $PayPal_config = new Give_When_PayPal_Helper();
        $PayPal = new GiveWhen_Angelleye_PayPal($PayPal_config->get_configuration());        
        $SECFields = array(
                'maxamt' => round($amount * 2,2),
                'returnurl' => site_url('?action=ec_return'),
                'cancelurl' => $cancel_page,
                'hdrimg' => 'https://www.angelleye.com/images/angelleye-paypal-header-750x90.jpg',
                'logoimg' => 'https://www.angelleye.com/images/angelleye-logo-190x60.jpg',
                'brandname' => 'Angell EYE',
                'customerservicenumber' => '816-555-5555',
        );
        $Payments = array();
        $Payment = array(
            'amt' => 0,            
            'custom' => 'amount_'.$amount.'|post_id_'.$post_id.'|user_id_'.$user_id
        );
        array_push($Payments, $Payment);
        
        $BillingAgreements = array();
        $Item = array(
                'l_billingtype' => 'MerchantInitiatedBilling',
                'l_billingagreementdescription' => $trigger_name,
                'l_paymenttype' => '',
                'l_billingagreementcustom' => 'give_when_'.$post_id
        );
        array_push($BillingAgreements, $Item);

        $PayPalRequestData = array(
            'SECFields' => $SECFields, 
            'Payments' => $Payments,
            'BillingAgreements' => $BillingAgreements,
        );
        $PayPalResult = $PayPal->SetExpressCheckout($PayPalRequestData);        
        if($PayPal->APICallSuccessful($PayPalResult['ACK']))
        {            
            echo json_encode(array('Ack'=>'Success','RedirectURL'=>$PayPalResult['REDIRECTURL']));
        }
        else
        {
            echo json_encode(array('Ack'=>'Failure','ErrorCode'=>$PayPalResult['L_ERRORCODE0'],'ErrorShort'=>$PayPalResult['L_SHORTMESSAGE0'],'ErrorLong'=>$PayPalResult['L_LONGMESSAGE0']));            
        }
        exit;
    }
    
}

AngellEYE_Give_When_Public_Display::init();
