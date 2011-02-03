<?php
class com_meego_website_injector
{
    public function inject_process(midgardmvc_core_request $request)
    {
        // We inject the template to provide MeeGo styling
        $request->add_component_to_chain(midgardmvc_core::get_instance()->component->get('com_meego_website'), true);
    }
}
?>
