<?php
class com_meego_website_controllers_avatar extends midgardmvc_helper_attachmentserver_controllers_base
{
    private $proxy = '';

    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
        if (!isset(midgardmvc_core::get_instance()->configuration->com_meego_website)) {
            return;
        }
        $settings = midgardmvc_core::get_instance()->configuration->com_meego_website;
        if (isset($settings['proxy']))
        {
            $this->proxy = $settings['proxy'];
        }
    }

    /**
     * Copy given file contents to given attachment
     *
     * @param midgard_attachment $attachment attachment
     * @param string $src fopened file or url
     * @param stream_context $context context
     */
    public function copy_file_to_attachment($src, &$attachment, $context)
    {

        $blob = midgardmvc_helper_attachmentserver_helpers::get_blob($attachment);
        if ($src)
        {
            $dst = $blob->get_handler('wb');
            midgardmvc_helper_attachmentserver_helpers::file_pointer_copy($src, $dst, true);
            $attachment->update();
        }
    }


    public function get_avatar(array $args)
    {
        $ar = array
        (
            'login' => $args['username'],
            'authtype' => midgardmvc_core::get_instance()->configuration->services_authentication_authtype
        );

        try
        {
            $user = new midgard_user($ar);
        }
        catch(Exception $e)
        {
            //User does not exist, send 404.
            throw new midgardmvc_exception_notfound("Avatar not found");
        }
        if ($user)
        {
            $attachments = $user->get_person()->list_attachments();

            //Check if attachement exists
            if (count($attachments) == 0)
            {
                //fetch avatar from meego.com
                $employeenumber = $user->get_person()->get_parameter('midgardmvc_core_services_authentication_ldap', 'employeenumber');
                $file = 'http://meego.com/sites/all/files/imagecache/user_pics/user_pics/picture-' . $employeenumber . '.png';
                $opts = array();

                if ($this->proxy)
                {
                    $opts = array('http' => array('proxy' => $this->proxy, 'request_fulluri' => true));
                }
                $context = stream_context_create($opts);
                $src = fopen($file, 'rb', false, $context);
                if ($src)
                {
                    $attachment = $user->get_person()->create_attachment('meego:avatar', 'meego:avatar', 'image/png');
                    //Does not work through proxy as is.
                    $this->copy_file_to_attachment($src, $attachment, $context);
                    $attachments[0] = $attachment;
                }
            }

            if (count($attachments) > 0)
            {
                //serve attachment
                $this->serve_attachment($attachments[0]);
            }
        }
        //redirect to default avatar
        midgardmvc_core::get_instance()->head->relocate('http://meego.com/sites/all/themes/meego/images/peep_skate.png');
    }
}
?>
