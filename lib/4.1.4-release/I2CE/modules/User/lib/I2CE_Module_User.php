<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* @package i2ce
* @subpackage user
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.0
* @since v4.1.0
* @filesource 
*/ 
/** 
* Class I2CE_Module_User
* 
* @access public
*/


class I2CE_Module_User extends I2CE_Module{
    /**
     * Checks to see if we are doing an auto-login
     */
    public static function doAutoLogin() {
        $userAccess= I2CE::getUserAccess();
        if (! $userAccess instanceof I2CE_UserAccess_Mechanism) {
            return false;
        }            
        return $userAccess->doAutoLogin();        
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
