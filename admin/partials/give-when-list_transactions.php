<?php

/**
 *
 * Display Transactions's Data
 *
 * @class       AngellEYE_Give_When_Post_types
 * @version	1.0.0
 * @package	give-when
 * @category	Class
 * @author      Angell EYE <service@angelleye.com>
 */
class AngellEYE_Give_When_Transactions_Table extends WP_List_Table {
    /**
     * Class Constructor
     * @since    1.0.0     
     */

    /**     * ***********************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     * ************************************************************************* */
    public function __construct() {

        parent::__construct([
            'singular' => __('Giver', 'angelleye_give_when'), //singular name of the listed records
            'plural' => __('Givers', 'angelleye_give_when'), //plural name of the listed records
            'ajax' => false //should this table support ajax?
        ]);
    }

    /**
     * Hook in methods
     * @since    1.0.0
     * @access   static
     */
    public static function init() {
        // add_action for the class
    }

    /**
     * Retrieve givers’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_transactions($per_page = 5, $page_number = 1) {

        global $wpdb;

        $sql = "SELECT  (SELECT usrmeta.meta_value from {$wpdb->prefix}usermeta as usrmeta where usrmeta.user_id = b.meta_value and usrmeta.meta_key = 'give_when_gec_payer_id') as PayPalPayerID,
             (SELECT usrmeta.meta_value from {$wpdb->prefix}usermeta as usrmeta where usrmeta.user_id =  b.meta_value and usrmeta.meta_key = 'give_when_gec_email') as user_paypal_email,
             (SELECT usr.display_name from {$wpdb->prefix}users as usr where usr.ID =  b.meta_value ) as user_display_name,
              pm.post_id,
              pm.meta_value as amount,
              b.meta_value as userId,
              c.meta_value as transactionId,
              t.meta_value as ppack,
              p.post_date as Txn_date
              FROM `{$wpdb->prefix}postmeta` as pm 
              left JOIN {$wpdb->prefix}postmeta b ON b.post_id = pm.post_id AND b.meta_key = 'give_when_transactions_wp_user_id'
              left JOIN {$wpdb->prefix}postmeta c ON c.post_id = pm.post_id AND c.meta_key = 'give_when_transactions_transaction_id'
              left JOIN {$wpdb->prefix}postmeta t ON t.post_id = pm.post_id AND t.meta_key = 'give_when_transactions_ack'
              JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id AND p.post_title Like '%GoalID:{$_REQUEST['post']}%'     
              WHERE pm.`post_id` IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE `meta_value` = '{$_REQUEST['post']}' AND `meta_key` = 'give_when_transactions_wp_goal_id')  ";

        $sql .= ' group by  p.ID';
        if (isset($_REQUEST['s'])) {
            $sql .= "  Having (( PayPalPayerID LIKE '%{$_REQUEST['s']}%' ) OR ( user_paypal_email LIKE '%{$_REQUEST['s']}%' ) OR ( user_display_name LIKE '%{$_REQUEST['s']}%' ) OR ( amount LIKE '%{$_REQUEST['s']}%' ) OR ( transactionId LIKE '%{$_REQUEST['s']}%' ) OR ( ppack LIKE '%{$_REQUEST['s']}%' ) ) ";
        }
        if(isset($_REQUEST['payment_status-filter'])){
          $sql .= "  Having (( ppack LIKE '{$_REQUEST['payment_status-filter']}' ) ) ";     
        }
        if (isset($_REQUEST['orderby'])) {
            if (!empty($_REQUEST['orderby'])) {
                $sql .= ' ORDER BY ' . $_REQUEST['orderby'];
            } else {
                /* by default we will add post time/post type time order by  */
                $sql .= ' ORDER BY user_display_name ';
            }
            $sql .=!empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            /* by default we will add post time/post type time order by  */
            $sql .= ' ORDER BY user_display_name ';
            $sql .=!empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }
        if(isset($_REQUEST['records_show-filter'])){
            $per_page = $_REQUEST['records_show-filter'];
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        $result_array = $wpdb->get_results($sql, 'ARRAY_A');

        return $result_array;
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_customer($id) {
        global $wpdb;

        $wpdb->delete(
                "{$wpdb->prefix}customers", [ 'ID' => $id], [ '%d']
        );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT  (SELECT usrmeta.meta_value from {$wpdb->prefix}usermeta as usrmeta where usrmeta.user_id = b.meta_value and usrmeta.meta_key = 'give_when_gec_payer_id') as PayPalPayerID,
             (SELECT usrmeta.meta_value from {$wpdb->prefix}usermeta as usrmeta where usrmeta.user_id =  b.meta_value and usrmeta.meta_key = 'give_when_gec_email') as user_paypal_email,
             (SELECT usr.display_name from {$wpdb->prefix}users as usr where usr.ID =  b.meta_value ) as user_display_name,
              pm.post_id,
              pm.meta_value as amount,
              b.meta_value as userId,
              c.meta_value as transactionId,
              t.meta_value as ppack,
              p.post_date as Txn_date
              FROM `{$wpdb->prefix}postmeta` as pm 
              left JOIN {$wpdb->prefix}postmeta b ON b.post_id = pm.post_id AND b.meta_key = 'give_when_transactions_wp_user_id'
              left JOIN {$wpdb->prefix}postmeta c ON c.post_id = pm.post_id AND c.meta_key = 'give_when_transactions_transaction_id'
              left JOIN {$wpdb->prefix}postmeta t ON t.post_id = pm.post_id AND t.meta_key = 'give_when_transactions_ack'    
              JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id AND p.post_title Like '%GoalID:{$_REQUEST['post']}%'     
              WHERE pm.`post_id` IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE `meta_value` = '{$_REQUEST['post']}' AND `meta_key` = 'give_when_transactions_wp_goal_id') ";
        $sql .= ' group by  p.ID';
        if (isset($_REQUEST['s'])) {
            $sql .= "  Having (( PayPalPayerID LIKE '%{$_REQUEST['s']}%' ) OR ( user_paypal_email LIKE '%{$_REQUEST['s']}%' ) OR ( user_display_name LIKE '%{$_REQUEST['s']}%' ) OR ( amount LIKE '%{$_REQUEST['s']}%' ) OR ( transactionId LIKE '%{$_REQUEST['s']}%' ) OR ( ppack LIKE '%{$_REQUEST['s']}%' ) ) ";
        }
        if(isset($_REQUEST['payment_status-filter'])){
          $sql .= "  Having (( ppack LIKE '{$_REQUEST['payment_status-filter']}' ) ) ";     
        }
        $wpdb->get_results($sql, 'ARRAY_A');
        return $wpdb->num_rows;
    }

    /** Text displayed when no giver's data is available */
    public function no_items() {
        _e('No Transactions avaliable.', 'angelleye_give_when');
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_name($item) {
//
//      // create a nonce
//      $delete_nonce = wp_create_nonce( 'sp_delete_customer' );
//
//      $title = '<strong>' . $item['name'] . '</strong>';
//
//      $actions = [
//        'delete' => sprintf( '<a href="#">Delete</a>')
//      ];
//
//      return $title . $this->row_actions( $actions );
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'transactionId':
                echo '<a href="' . site_url() . '/wp-admin/?page=give_when_givers&post=' . $_REQUEST['post'] . '&view=GetTransactionDetail&txn_id=' . $item['transactionId'] . '">' . $item['transactionId'] . '</a>';
                break;
            case 'user_display_name':
                echo $item['user_display_name'];
                break;
            case 'amount' :
                echo $item['amount'];
                break;
            case 'PayPalPayerID' :
                echo $item['PayPalPayerID'];
                break;
            case 'user_paypal_email' :
                echo $item['user_paypal_email'];
                break;
            case 'ppack' :
                echo $item['ppack'];
                break;
            case 'Txn_date' :
                echo date('Y-m-d', strtotime($item['Txn_date']));
                break;
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['user_id']
        );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'transactionId' => __('Transaction ID', 'angelleye_give_when'),
            'user_display_name' => __('Name', 'angelleye_give_when'),
            'amount' => __('Amount', 'angelleye_give_when'),
            'user_paypal_email' => __('PayPal Email ID', 'angelleye_give_when'),
            'PayPalPayerID' => __('PayPal Payer ID', 'angelleye_give_when'),
            'ppack' => __('Payment Status', 'angelleye_give_when'),
            'Txn_date' => __('Payment Date', 'angelleye_give_when')
        ];

        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'transactionId' => array('transactionId', true),
            'user_display_name' => array('user_display_name', true),
            'amount' => array('amount', true),
            'user_paypal_email' => array('user_paypal_email', true),
            'PayPalPayerID' => array('PayPalPayerID', true)
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-retry' => 'Retry'
        ];

        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
         
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('transactions_per_page', 5);
        $current_page = $this->get_pagenum();
        if(isset($_REQUEST['records_show-filter'])){
            $per_page = $_REQUEST['records_show-filter'];
        }
        $total_items = self::record_count();
        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ]);


        $this->items = self::get_transactions($per_page, $current_page);
        ?>
        <!-- for transection filter -->
        <script type="text/javascript">
          jQuery('.ewc-filter-cat').live('change', function(){
              var catFilter = jQuery(this).val();
              if( catFilter != '' ){                  
                  document.location.href = '<?php echo admin_url('?'.$_SERVER['QUERY_STRING']); ?>&payment_status-filter='+catFilter;    
              }
          });
          jQuery('.ewc-filter-num').live('change', function(){
              var rsFilter = jQuery(this).val();
              if( rsFilter != '' ){                  
                  document.location.href = '<?php echo admin_url('?'.$_SERVER['QUERY_STRING']); ?>&records_show-filter='+rsFilter;
              }
          });
        </script>
        
        <?php
    }

    public function process_bulk_action() {
        //echo "<pre>";
       // print_r($_REQUEST);
       // exit;
        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);

            if (!wp_verify_nonce($nonce, 'sp_delete_customer')) {
                die('Go get a life script kiddies');
            } else {
                self::delete_customer(absint($_GET['customer']));

                wp_redirect(esc_url(add_query_arg()));
                exit;
            }
        }

        // If the delete bulk action is triggered
        if (( isset($_POST['action']) && $_POST['action'] == 'bulk-delete' ) || ( isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql($_POST['bulk-delete']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_customer($id);
            }

            wp_redirect(esc_url(add_query_arg()));
            exit;
        }
    }

    public function extra_tablenav($which) {
        global $wpdb, $testiURL, $tablename, $tablet;
        $move_on_url = '&post=' . $_REQUEST['post'] . '&view=ListTransactions&payment_status-filter=';
        $selected = "selected='selected'";
        $status_filter = !empty($_REQUEST['payment_status-filter']) ? $_REQUEST['payment_status-filter'] : '';
        $rs_filter = !empty($_REQUEST['records_show-filter']) ? $_REQUEST['records_show-filter'] : '';
        if ($which == "top") {
            ?>
            <div class="alignleft actions bulkactions">
                <select name="cat-filter" class="ewc-filter-cat">
                    <option value="">Filter by Payment Status</option>
                    <option value="<?php echo $move_on_url; ?>Success" <?php if ($status_filter == "Success") {
                echo $selected;
            } ?>>Success</option>
                    <option value="<?php echo $move_on_url; ?>Failure" <?php if ($status_filter == "Failure") {
                echo $selected;
            } ?>>Failed</option>
                    <option value="<?php echo $move_on_url; ?>pending" <?php if ($status_filter == "pending") {
                echo $selected;
            } ?>>Pending</option>
                </select>                            
                <select name="number_of_trans" class="ewc-filter-num">
                    <option value="">Show Number of Records</option>
                    <option value="10" <?php if($rs_filter === '10') { echo $selected; } ?>>10</option>
                    <option value="25" <?php if($rs_filter === '25') { echo $selected; } ?>>25</option>
                    <option value="50" <?php if($rs_filter === '50') { echo $selected; } ?>>50</option>
                    <option value="100" <?php if($rs_filter === '100') { echo $selected; } ?>>100</option>
                </select>
            </div>
            <?php
        }
        if ($which == "bottom") {
            //The code that goes after the table is there
        }
    }

}

AngellEYE_Give_When_Transactions_Table::init();
