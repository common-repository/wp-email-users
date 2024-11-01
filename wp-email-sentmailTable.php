<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class SentEmailTable extends WP_List_Table
{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'sentemail',
            'plural'   => 'sentemails',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_weu_from_name($item)
    {
        $actions = array(
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['weu_sent_id'], 'Delete'),
        );

        return sprintf(
            '%s %s',
            $item['weu_from_name'],
            $this->row_actions($actions)
        );
    }

    function column_weu_status($item)
    {
        return !empty($item['weu_status']) ? 'Sent' : 'Failed';
    }

    function column_weu_seen($item)
    {
        return !empty($item['weu_seen']) ? 'Yes' : 'No';
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['weu_sent_id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb'                    => '<input type="checkbox" />',
            // 'weu_sent_id'       => 'weu_sent_id',
            'weu_from_name'         => 'From Name',
            'weu_from_email'        => 'From Email',
            'to_email'              => 'To Email',
            'weu_email_subject'     => 'Subject',
            'weu_sent_type'         => 'Email Type',
            'weu_to_type'           => 'User Type',
            'weu_sent_date_time'    => 'Date-Time',
            'weu_status'            => 'Status',
            'weu_seen'              => 'Seen',
            'weu_seen_count'        => 'Seen Count'
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'weu_from_name'         => 'From Name',
            'weu_from_email'        => 'From Email',
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_sent_email';

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE weu_sent_id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_sent_email';

        $per_page = 10;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(weu_sent_id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'weu_sent_id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}
