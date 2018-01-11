<?php

class iHRIS_Module extends I2CE_Module {



    public function showUserNames($node,$template) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {
            $user = $template->getUser();        
            $namesNode = $template->appendFileByNode('user_names.html','span',$node);
            $template->setDisplayDataImmediate('user_firstname', $user->firstname,$namesNode);
            $template->setDisplayDataImmediate('user_lastname', $user->lastname,$namesNode);
            return;
        }
    }


    public function getUserNames() {
        $user = new I2CE_User();
        return $user->displayName();
    }
            


    public function welcomeNamedUser($node,$template,$named_welcome_str,$unamed_welcome_str = '') {
        if (!$template instanceof I2CE_Template && !$node instanceof DOMNode) {
            return;
        }
        $user = new I2CE_User();
        if ($named_welcome_str && $user->logged_in() && (  $name = $user->displayName())) {
            $text =  sprintf($named_welcome_str,$name);
        } else {
            $text = $unamed_welcome_str;
        }
        $t_node = $template->createTextNode($text);
        $node->appendChild($t_node);
    }
            

    
    public function showUserRole($node ,$template) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {
            $node->appendChild($template->createTextNode($this->getUserRole()));
        }
    }

    public function getUserRole() {
        $user = new I2CE_User();
        if ($user->role) {
            return I2CE_User_Form::getRoleNameFromShortName($user->role);
        } else {
            return null;
        }
    }
     

        
     
    public function showModuleVersion($node,$template, $module) {
        if ($template instanceof I2CE_Template && $node instanceof DOMNode) {

            $node->appendChild($template->createTextNode($this->getModuleVersion($module)));
        }
    }

    public function getModuleVersion($module) {
        $version = '';
        I2CE::getConfig()->setIfIsSet($version,"/config/data/$module/version");
        return $version;
    }


}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
