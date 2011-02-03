<?php
class com_meego_website_controllers_avatar extends midgardmvc_helper_attachmentserver_controllers_base
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_avatar(array $args)
    {
        $ar = array('login' => $args['username'], 'authtype' => 'LDAP');

        $user = new midgard_user($ar);
        if ($user)
        {
            $attachments = $user->get_person()->list_attachments();
            //Check if attachement exists
            if (count($attachments) == 0)
            {
                //fetch avatar from meego.com
                $employeenumber = $user->get_person()->get_parameter('midgardmvc_core_services_authentication_ldap', 'employeenumber');
                $attachment = $user->get_person()->create_attachment('meego:avatar', 'meego:avatar', 'image/png');
                //Does not work through proxy as is.
//                midgardmvc_helper_attachmentserver_helpers::copy_file_to_attachment('http://meego.com/sites/all/files/imagecache/user_pics/user_pics/picture-' . $employeenumber . '.png', $attachment);
//                $attachments[0] = $attachment;
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
