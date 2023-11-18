<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class PC_W2P_UTILITIES {
    public static function get_font_subsets() {
        return array(
            'all'   =>  array(
                'name'  =>  'All language',
                'preview_text'  =>  'Abc Xyz',
                'default_font'  =>  'Roboto'
            ),
            'arabic'   =>  array(
                'name'  =>  'Arabic',
                'preview_text'  =>  'ءيوهن',
                'default_font'  =>  'Cairo'
            ),
            'bengali'   =>  array(
                'name'  =>  'Bengali',
                'preview_text'  =>  'অআইঈউ',
                'default_font'  =>  'Hind Siliguri'
            ),
            'cyrillic'   =>  array(
                'name'  =>  'Cyrillic',
                'preview_text'  =>  'БВГҐД',
                'default_font'  =>  'Roboto'
            ),
            'cyrillic-ext'   =>  array(
                'name'  =>  'Cyrillic Extended',
                'preview_text'  =>  'БВГҐД',
                'default_font'  =>  'Roboto'
            ),
            'chinese-simplified'   =>  array(
                'name'  =>  'Chinese (Simplified)',
                'preview_text'  =>  '一二三四五',
                'default_font'  =>  'ZCOOL XiaoWei'
            ),
            'devanagari'   =>  array(
                'name'  =>  'Devanagari',
                'preview_text'  =>  'आईऊऋॠ',
                'default_font'  =>  'Noto Sans'
            ),
            'greek'   =>  array(
                'name'  =>  'Greek',
                'preview_text'  =>  'αβγδε',
                'default_font'  =>  'Roboto'
            ),
            'greek-ext'   =>  array(
                'name'  =>  'Greek Extended',
                'preview_text'  =>  'αβγδε',
                'default_font'  =>  'Roboto'
            ),
            'gujarati'   =>  array(
                'name'  =>  'Gujarati',
                'preview_text'  =>  'આઇઈઉઊ',
                'default_font'  =>  'Shrikhand'
            ),
            'gurmukhi'   =>  array(
                'name'  =>  'Gurmukhi',
                'preview_text'  =>  'ਆਈਊਏਐ',
                'default_font'  =>  'Baloo Paaji'
            ),
            'hebrew'   =>  array(
                'name'  =>  'Hebrew',
                'preview_text'  =>  'אבגדה',
                'default_font'  =>  'Arimo'
            ),
            'japanese'   =>  array(
                'name'  =>  'Japanese',
                'preview_text'  =>  '一二三四五',
                'default_font'  =>  'Sawarabi Mincho'
            ),
            'kannada'   =>  array(
                'name'  =>  'Kannada',
                'preview_text'  =>  'ಅಆಇಈಉ',
                'default_font'  =>  'Baloo Tamma'
            ),
            'khmer'   =>  array(
                'name'  =>  'Khmer',
                'preview_text'  =>  'កខគឃង',
                'default_font'  =>  'Hanuman'
            ),
            'korean'   =>  array(
                'name'  =>  'Korean',
                'preview_text'  =>  '가개갸거게',
                'default_font'  =>  'Nanum Gothic'
            ),
            'latin'   =>  array(
                'name'  =>  'Latin',
                'preview_text'  =>  'Abc Xyz',
                'default_font'  =>  'Roboto'
            ),
            'latin-ext'   =>  array(
                'name'  =>  'Latin Extended',
                'preview_text'  =>  'Abc Xyz',
                'default_font'  =>  'Roboto'
            ),
            'malayalam'   =>  array(
                'name'  =>  'Malayalam',
                'preview_text'  =>  'അആഇഈഉ',
                'default_font'  =>  'Baloo Chettan'
            ),
            'myanmar'   =>  array(
                'name'  =>  'Myanmar',
                'preview_text'  =>  'ကခဂဃင',
                'default_font'  =>  'Padauk'
            ),
            'oriya'   =>  array(
                'name'  =>  'Oriya',
                'preview_text'  =>  'ଅଆଇଈଉ',
                'default_font'  =>  'Baloo Bhaina'
            ),
            'sinhala'   =>  array(
                'name'  =>  'Sinhala',
                'preview_text'  =>  'අආඇඈඉ',
                'default_font'  =>  'Abhaya Libre'
            ),
            'tamil'   =>  array(
                'name'  =>  'Tamil',
                'preview_text'  =>  'க்ங்ச்ஞ்ட்',
                'default_font'  =>  'Catamaran'
            ),
            'telugu'   =>  array(
                'name'  =>  'Telugu',
                'preview_text'  =>  'అఆఇఈఉ',
                'default_font'  =>  'Gurajada'
            ),
            'thai'   =>  array(
                'name'  =>  'Thai',
                'preview_text'  =>  'กขคฆง',
                'default_font'  =>  'Kanit'
            ),
            'vietnamese'   =>  array(
                'name'  =>  'Vietnamese',
                'preview_text'  =>  'Abc Xyz',
                'default_font'  =>  'Roboto'
            )
        );
    }
    public static function check_hpos_enabled() {
        return get_option( 'woocommerce_custom_orders_table_enabled' ) === 'yes';
    }
    public static function update_post_meta( $post_id, $meta_key, $meta_value, $need_save = true ) {
        if(self::check_hpos_enabled()) {
            $order = wc_get_order($post_id);

            if($order) {
                $order->update_meta_data( $meta_key, $meta_value );

                if($need_save) $order->save();
                return;
            }
        }
        update_post_meta( $post_id, $meta_key, $meta_value );
    }
    public static function get_post_meta( $post_id, $meta_key, $single ) {
        if(self::check_hpos_enabled()) {
            $order = wc_get_order($post_id);

            if($order) {
                return $order->get_meta( $meta_key );
            }
        }
        return get_post_meta( $post_id, $meta_key, $single );
    }
    public static function add_post_meta( $post_id, $meta_key, $meta_value, $need_save = true  ) {
        if(self::check_hpos_enabled()) {
            $order = wc_get_order($post_id);

            if($order) {
                $order->add_meta_data( $meta_key, $meta_value );

                if($need_save) $order->save();
                return;
            }
        }
      
        add_post_meta( $post_id, $meta_key, $meta_value );
    }
    public static function delete_post_meta( $post_id, $meta_key, $meta_value, $need_save = true  ) {
        if(self::check_hpos_enabled()) {
            $order = wc_get_order($post_id);

            if($order) {
                $order->delete_meta_data( $meta_key, $meta_value );

                if($need_save) $order->save();
                return;
            }
        } 
        delete_post_meta( $post_id, $meta_key, $meta_value );
    }
}
