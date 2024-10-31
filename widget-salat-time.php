<?php
// Creating the widget
class salattime_widget extends WP_Widget {
	function __construct() {
            parent::__construct('salattime_widget',__('Muslim Salat Time', 'mst_widget_domain'),array( 'description' => __( 'Muslim Salat Time Widget', 'mst_widget_domain' ),));
	}
	 
     

	public function widget( $args, $instance ) {
        global $wpdb;
        $title = apply_filters( 'widget_title', $instance['title'] );
    	echo $args['before_widget'];
    	if ( ! empty( $title ) ) {
        	echo $args['before_title'] . $title . $args['after_title'];
    	}
        $names_arr = array("Fajr","Sunrise","Dhuhr","Asr","Sunset","Maghrib","Isha");
        
        $timeZone =  get_option('mst_time_zone');
        $latitude = get_option('mst_latitude');
        $longitude = get_option('mst_longitude');
        $mst_method = get_option('mst_method');
        $mst_total_days = get_option('mst_total_days');
        $mst_back_days = get_option('mst_back_days');
        
        
        $prayTime = new PrayTime($mst_method);
        $prayTime->setAsrMethod(1);
        $prayTime->setTimeFormat(2); 
        
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        $current_time = mktime(0,0,0,$month,$day,$year);
        
        $start_time = mktime(0,0,0,$month, ($day - $mst_back_days) ,$year);
        
        $end_time = mktime(0,0,0,$month, ($day + ($mst_total_days - $mst_back_days)) , $year);
        
        
        
        $date = strtotime($year . '-' . $month . '-1');
        $endDate = strtotime($next_year . '-' . $next_month . '-1');
        
        
        $date = $start_time;
        $endDate = $end_time;
        $times = array();
        while ($date < $endDate) {
            $times[] = $prayTime->getPrayerTimes($date, $latitude, $longitude, $timeZone);
            $date += 24* 60* 60;  // next day
        }
        //print '<pre>';
        //print_r($times);
        //print '</pre>';
        
        $html = '';
        $html .= '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="salat-table">';
        $html .=  '<tr>';
        $html .= '<th>Salat</th>';
        
        for($j = 0; $j < $mst_total_days; ++$j) {
            $html .=  '<th>' . date('d/m',$start_time) . '</th>';
            $start_time += 24 * 60 * 60;  // next day
        }
        $html .=  '</tr>';
        
        
        
        for($n = 0; $n < count($names_arr); ++$n) {
            $html .= '<tr>';
            $html .= '<td>';
            $html .= $names_arr[$n];
            $html .= '</td>';
            for($d = 0; $d < $mst_total_days; ++$d) {
                $html .=  '<td class="right">';
                $html .=  $times[$d][$n];
                $html .=  '</td>';    
            }
            $html .= '</tr>';
        }

        
        $html .= '</table>';
        print $html;
        
        echo $args['after_widget'];
	}
	         
	
	public function form( $instance ) {
        
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'wpb_widget_domain' );
        }
    
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
	}
	     
	
	public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
    
} 
	 

function wpb_load_widget() {
    register_widget( 'salattime_widget' );
}

add_action( 'widgets_init', 'wpb_load_widget' );