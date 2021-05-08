<?php
namespace A3Rev\A3HidePostPageTitle;

class Main
{

    private $a3hpt_slug = 'a3hpt_headertitle';
    private $a3hpt_selector = '.entry-title';
    private $a3hpt_adminselector = '.edit-post-visual-editor__post-title-wrapper, #titlewrap';
    private $title;
    private $a3hpt_afthead = false;

    /*Constructor*/
    function __construct()
    {

    	add_action( 'wp_default_scripts', array( $this, '_default_scripts' ), 11 );

        add_action('add_meta_boxes', array(
            $this,
            'a3hpt_hptaddbox'
        ));
        add_action('save_post', array(
            $this,
            'a3hpt_hptsave'
        ));
        add_action('delete_post', array(
            $this,
            'a3hpt_hptdelete'
        ));
        add_action('wp_head', array(
            $this,
            'a3hpt_hptheadinsert'
        ));
        add_action('admin_enqueue_scripts', array(
            $this,
            'a3hpt_hptadmininsert'
        ));
        add_action('the_title', array(
            $this,
            'a3hpt_hptwraptitle'
        ));
        add_action('wp_enqueue_scripts', array(
            $this,
            'a3hpt_hptloadscripts'
        ));
    }

    /*Function HPT hidden*/
    private function a3hpt_ishidden()
    {
        if (is_singular())
        {
            global $post;
            $toggle = get_post_meta($post->ID, $this->a3hpt_slug, true);
            if ((bool)$toggle)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /*Function hptheadinseart for Hiding page title*/
    public function a3hpt_hptheadinsert()
    {
        if ($this->a3hpt_ishidden())
        { ?> <!-- Hide Page Title -->
          <script type="text/javascript">
			jQuery(document).ready(function($){
				if( $('<?php echo $this->a3hpt_selector; ?>').length != 0 ) {
					$('<?php echo $this->a3hpt_selector; ?> span.<?php echo $this->a3hpt_slug; ?>').parents('<?php echo $this->a3hpt_selector; ?>:first').hide();
			    } else {
				  	$('h1 span.<?php echo $this->a3hpt_slug; ?>').parents('h1:first').hide();
				  	$('h2 span.<?php echo $this->a3hpt_slug; ?>').parents('h2:first').hide();
			   	}
			});
          </script><noscript><style type="text/css"> <?php echo $this->a3hpt_selector; ?> { display:none !important; }</style></noscript>
         <!-- END Hide Page Title-->
    	<?php
        }
        $this->a3hpt_afthead = true;
    }

    private function a3hpt_isadminhidden()
    {
        global $pagenow;
		if (( $pagenow == 'post.php' ) || ( (get_post_type() == 'post') || (get_post_type() == 'page') ) ) {

			global $post;
	        $toggle = get_post_meta($post->ID, $this->a3hpt_slug, true);
            if ((bool)$toggle)
            {
                return true;
            }
            else
            {
                return false;
            }
	      
		}else{
			return false;
		}
    }

    function _default_scripts( &$scripts ){
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '';
	    $scripts->add( 'a3hpt_script', A3_HPPT_JS_URL . '/a3-hide-post-page-title'.$suffix.'.js', array( 'jquery' ), '1.0.0', true );
	}

    public function a3hpt_hptadmininsert()
    {
    	global $pagenow;
		if (( $pagenow == 'post.php' ) || ( (get_post_type() == 'post') || (get_post_type() == 'page') ) ) {

			$_paramaters =  array(
	        	'a3hpt_slug' 			=> $this->a3hpt_slug,
	        	'a3hpt_selector' 		=> $this->a3hpt_selector,
	        	'a3hpt_adminselector' 	=> $this->a3hpt_adminselector,
			);

			wp_enqueue_script( 'a3hpt_script' );

			wp_localize_script( 'a3hpt_script', 'a3hpt_paramaters', $_paramaters );
	    }
    }

    /*Function hptaddbox*/
    public function a3hpt_hptaddbox()
    {
        $posttypes = array(
            'post',
            'page'
        );
        $args = array(
            'public' => true,
            '_builtin' => false,
        );

        $output = 'names';
        $operator = 'and';

        $post_types = get_post_types($args, $output, $operator);

        foreach ($post_types as $post_type)
        {

            $posttypes[] = $post_type;

        }

        foreach ($posttypes as $posttype)
        {
            add_meta_box($this->a3hpt_slug, 'Hide Page and Post Title', array(
                $this,
                'build_hptbox'
            ) , $posttype, 'side');
        }
    }

    /*Adding box in admindashboard*/
    public function build_hptbox($post)
    {
        $value = get_post_meta($post->ID, $this->a3hpt_slug, true);
        $checked = '';
        if ((bool)$value)
        {
            $checked = ' checked="checked"';
        }
        wp_nonce_field($this->a3hpt_slug . '_dononce', $this->a3hpt_slug . '_noncename'); ?>
		<label><input type="checkbox" name="<?php echo $this->a3hpt_slug; ?>" <?php echo $checked; ?> /> Hide the title.</label><?php
    }

    /*HPT wraptitle function*/
    public function a3hpt_hptwraptitle($hptcontent)
    {
        if ($this->a3hpt_ishidden() && $hptcontent == $this->title && $this->a3hpt_afthead)
        {
            $hptcontent = '<span class="' . $this->a3hpt_slug . '">' . $hptcontent . '</span>';
        }
        return $hptcontent;
    }

    /*Script*/
    public function a3hpt_hptloadscripts()
    {
        global $post;
        $this->title = $post->post_title;
        if ($this->a3hpt_ishidden())
        {
            wp_enqueue_script('jquery');
        }
    }

    /*Autosave metabox*/
    public function a3hpt_hptsave($postID)
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !isset($_POST[$this->a3hpt_slug . '_noncename']) || !wp_verify_nonce($_POST[$this->a3hpt_slug . '_noncename'], $this->a3hpt_slug . '_dononce'))
        {
            return $postID;
        }
        $old = get_post_meta($postID, $this->a3hpt_slug, true);
        $new = $_POST[$this->a3hpt_slug];
        if ($old)
        {
            if (is_null($new))
            {
                delete_post_meta($postID, $this->a3hpt_slug);
            }
            else
            {
                update_post_meta($postID, $this->a3hpt_slug, $new, $old);
            }
        }
        elseif (!is_null($new))
        {
            add_post_meta($postID, $this->a3hpt_slug, $new, true);
        }
        return $postID;
    }

    /*Delete metabox */
    public function a3hpt_hptdelete($postID)
    {
        delete_post_meta($postID, $this->a3hpt_slug);
        return $postID;
    }
    public function set_a3hpt_selector($a3hpt_selector)
    {
        if (isset($a3hpt_selector) && is_string($a3hpt_selector))
        {
            $this->a3hpt_selector = $a3hpt_selector;
        }
    }

}
?>