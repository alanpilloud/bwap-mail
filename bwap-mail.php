<?php
/*
Plugin Name: BWAP Mail
Plugin URI: http://alanpilloud.github.io
Description: Add a simple way to use your bootstrap form with ajax
Version: 0.0.2
Author: Bureau Web Alan Pilloud
Author URI: http://alanpilloud.github.io
*/

defined( 'ABSPATH' ) or die;

require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 ('https://kernl.us/api/v1/updates/5680505a086e629607788b83/', __FILE__, 'bwap-mail', 1);

if (!class_exists('BwapMail')) {

    add_action( 'wp_ajax_sendform', array( 'BwapMail', 'sendform_callback') );
    add_action( 'wp_ajax_nopriv_sendform', array( 'BwapMail', 'sendform_callback') );

    class BwapMail
    {
        protected $tag = 'bwap-mail';
        protected $name = 'BWAP Mail';
        protected $version = '0.0.2';

        public function __construct()
        {
            add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
        }

        public function shortcode( $atts, $content = null )
        {
            $this->enqueue();

            return '<div id="form-msg" style="display:none" data-error="Une erreur a eu lieu pendant l\'envoi de votre message" data-required="Certains champs n\'ont pas été remplis."></div>';
        }

        protected function enqueue()
        {
            $plugin_path = plugin_dir_url( __FILE__ );
            wp_enqueue_script($this->tag, $plugin_path . 'script.js', null, $this->version, true);
        }

        public function sendform_callback()
        {
            $return = array(
                'status'=>1,
                'msg'=> 'Votre message a bien été envoyé. Nous y répondrons au plus vite.',
            );
            $html = array();
            $data = filter_input_array(INPUT_POST);

            $info = pathinfo($_FILES['userFile']['name']);
            $ext = $info['extension']; // get the extension of the file
            $newname = "newname.".$ext;

            $target = 'images/'.$newname;

            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false) {
                $return['status'] = 0;
                $return['msg'] = __('L\'adresse email indiquée n\'est pas valide.','bwap-mail');
            }

            if (count($_FILES) > 0) {
                $upload_dir = wp_upload_dir();
                $uploadpath = $upload_dir['basedir'].DIRECTORY_SEPARATOR.'bwap-mail';
                if (!is_dir($uploadpath)) {
                    mkdir($uploadpath);
                }

                foreach ($_FILES as $k => $file) {
                    if ($file['error'] == 0) {
                        $filename = uniqid().'-'.$file['name'];
                        $filepath = $uploadpath.DIRECTORY_SEPARATOR.$filename;
                        $moved = move_uploaded_file($file['tmp_name'], str_replace('/',DIRECTORY_SEPARATOR,$filepath));
                        if (!$moved) {
                            $return['status'] = 0;
                            $return['msg'] = __('Le fichier n\'a pas pu être envoyé. Veuillez essayer à nouveau.','bwap-mail');
                        } else {
                            $html[] = '<b>'.str_replace('_',' ',$k).' :</b> '.$upload_dir['baseurl'].'/bwap-mail/'.$filename;
                        }
                    }
                }
            }

            if ($return['status'] == 1) {
                foreach($data as $k => $v) {
                    if (!empty($v)) {
                        $html[] = '<b>'.str_replace('_',' ',$k).' :</b> '.nl2br(stripslashes($v));
                    }
                }

                $send = wp_mail(get_option('admin_email'), 'Contact du site', implode('<br/>',$html), array(
                    'From: '.get_option('blogname').' <'.get_option('admin_email').'>',
                    'Reply-to: '.$data['email'],
                    'Content-type: text/html; charset=UTF-8'
                ));

                if ($send === false) {
                    $return['status'] = 0;
                    $return['msg'] = __('Il y a eu une erreur lors de l\'envoi. Merci de bien vouloir essayer à nouveau.<br/>'.implode('<br/>',$html),'bwap-mail');
                }
            }
            die(json_encode($return));
        }
    }
    new BwapMail;
}
