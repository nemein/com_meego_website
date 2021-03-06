<?php
abstract class com_meego_website_controllers_avatar extends midgardmvc_helper_attachmentserver_controllers_base
{
    public function __construct(midgardmvc_core_request $request)
    {
        $this->request = $request;
    }

    public function get_avatar(array $args)
    {
        $storage = new midgard_query_storage('midgard_user');
        $q = new midgard_query_select($storage);
        $cnstr1 = new midgard_query_constraint(
                         new midgard_query_property('username', $storage),
                         'LIKE',
                         new midgard_query_value('%' . $args['username'] .'%')
                      );

        $q->execute();
        $cnt = $q->get_results_count();

        if ($cnt > 0)
        {
            $user = $q->list_objects();
            if ($user[0])
            {
                $attachments = $user[0]->list_attachments();
                //Check if attachement exists
                if (count($attachments) == 0)
                {
                    //fetch avatar from meego.com
                    $employeenumber = $user[0]->get_person()->get_parameter('midgardmvc_core_services_authentication_ldap', 'employeenumber');

                    $attachment = $user[0]->create_attachment('meego:avatar', 'meego:avatar', 'image/png');
                    midgardmvc_helper_attachmentserver::copy_file_to_attachment('http://meego.com/sites/all/files/imagecache/user_pics/user_pics/picture-' . $employeenumber . '.png', $attachment);
                    $attachments[0] = $attachment;
                }
                if (count($attachments) > 0)
                {
                    //serve attachment
                    $this->serve_attachement($attachments[0]);
                    return;
                }
            }
        }
        //redirect to default avatar
         midgardmvc_core::get_instance()->head->relocate('http://meego.com/sites/all/themes/meego/images/peep_skate.png');
    }
}
