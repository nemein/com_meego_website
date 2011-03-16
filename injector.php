<?php
class com_meego_website_injector
{
    var $mvc = null;
    var $request = null;

    public function __construct()
    {
        $this->mvc = midgardmvc_core::get_instance();
    }

    public function inject_process(midgardmvc_core_request $request)
    {
        // We inject the template to provide MeeGo styling
        $request->add_component_to_chain($this->mvc->component->get('com_meego_website'), true);
        $this->request = $request;
        // set this to false to avoid TAL complaints
        // in case other components' injectors do not provide breadcrumb
        $request->set_data_item('breadcrumb', false);
    }

    /**
     * Some template hack
     */
    public function inject_template(midgardmvc_core_request $request)
    {
        $route = $request->get_route();

        $request->set_data_item('admin', false);

        if ($this->mvc->authentication->is_user())
        {
            if ($this->mvc->authentication->get_user()->is_admin())
            {
                $request->set_data_item('admin', true);
            }
        }
    }
}
?>
