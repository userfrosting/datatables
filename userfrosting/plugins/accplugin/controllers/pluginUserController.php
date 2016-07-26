<?php

namespace UserFrosting\accPlugin;

/**
 * UserController Class
 *
 * Controller class for /users/* URLs.  Handles user-related activities, including listing users, CRUD for users, etc.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class pluginUserController extends \UserFrosting\UserController {

    /**
     * Renders the form for editing an existing user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the form, which can be embedded in other pages.
     * The form can be rendered in "modal" (for popup) or "panel" mode, depending on the value of the GET parameter `render`.
     * For each field, we will first check if the currently logged-in user has permission to update the field.  If so,
     * the field will be rendered as editable.  If not, we will check if they have permission to view the field.  If so,
     * it will be displayed but disabled.  If they have neither permission, the field will be hidden.
     * This page requires authentication.
     * Request type: GET
     * @param int $user_id the id of the user to edit.
     */
    public function formUserEdit($user_id){
        // Get the user to edit
        $target_user = \UserFrosting\User::find($user_id);

        // Access-controlled resource
        if (!$this->_app->user->checkAccess('uri_users') && !$this->_app->user->checkAccess('uri_group_users', ['primary_group_id' => $target_user->primary_group_id])){
            $this->_app->notFound();
        }

        $get = $this->_app->request->get();

        if (isset($get['render']))
            $render = $get['render'];
        else
            $render = "modal";

        $render = "panel";
        // Get a list of all groups
        $groups = \UserFrosting\Group::get();

        // Get a list of all locales
        $locale_list = $this->_app->site->getLocales();

        // Determine which groups this user is a member of
        $user_groups = $target_user->getGroups();
        foreach ($groups as $group){
            $group_id = $group->id;
            $group_list[$group_id] = $group->export();
            if (isset($user_groups[$group_id]))
                $group_list[$group_id]['member'] = true;
            else
                $group_list[$group_id]['member'] = false;
        }

        if ($render == "modal")
            $template = "components/common/user-info-modal.twig";
        else
            $template = "components/common/user-info-panel.twig";

        $template = "user-edit.html.twig";
        // Determine authorized fields
        $fields = ['display_name', 'email', 'title', 'locale', 'groups', 'primary_group_id'];
        $show_fields = [];
        $disabled_fields = [];
        $hidden_fields = [];
        foreach ($fields as $field){
            if ($this->_app->user->checkAccess("update_account_setting", ["user" => $target_user, "property" => $field]))
                $show_fields[] = $field;
            else if ($this->_app->user->checkAccess("view_account_setting", ["user" => $target_user, "property" => $field]))
                $disabled_fields[] = $field;
            else
                $hidden_fields[] = $field;
        }

        // Always disallow editing username
        $disabled_fields[] = "user_name";

        // Load validator rules
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/user-update.json");
        $this->_app->jsValidator->setSchema($schema);

        $this->_app->render($template, [
            "box_id" => 'box_'.$user_id,
            "box_title" => "Edit User",
            "submit_button" => "Update user",
            "form_action" => $this->_app->site->uri['public'] . "/users/u/$user_id",
            "target_user" => $target_user,
            "groups" => $group_list,
            "locales" => $locale_list,
            "fields" => [
                "disabled" => $disabled_fields,
                "hidden" => $hidden_fields
            ],
            "buttons" => [
                "hidden" => [
                    "edit", "enable", "delete", "activate"
                ]
            ],
            "user_validators" => $this->_app->jsValidator->rules(),
            "validators" => $this->_app->jsValidator->rules()
        ]);
    }
}
