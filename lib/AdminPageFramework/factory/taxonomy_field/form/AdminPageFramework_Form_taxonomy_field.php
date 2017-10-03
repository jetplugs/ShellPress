<?php 
/**
	Admin Page Framework v3.8.15 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/shellpress>
	Copyright (c) 2013-2017, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class SP_v1_0_8_AdminPageFramework_Form_taxonomy_field extends SP_v1_0_8_AdminPageFramework_Form {
    public $sStructureType = 'taxonomy_field';
    public function get() {
        $this->sCapability = $this->callback($this->aCallbacks['capability'], '');
        if (!$this->canUserView($this->sCapability)) {
            return '';
        }
        $this->_formatElementDefinitions($this->aSavedData);
        $_oFieldsets = new SP_v1_0_8_AdminPageFramework_Form_View___FieldsetRows($this->getElementAsArray($this->aFieldsets, '_default'), null, $this->aSavedData, $this->getFieldErrors(), $this->aFieldTypeDefinitions, $this->aCallbacks, $this->oMsg);
        return $_oFieldsets->get();
    }
}
