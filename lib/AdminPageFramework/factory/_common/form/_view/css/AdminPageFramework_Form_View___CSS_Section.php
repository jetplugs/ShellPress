<?php 
/**
	Admin Page Framework v3.8.15 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/shellpress>
	Copyright (c) 2013-2017, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class SP_v1_0_8_AdminPageFramework_Form_View___CSS_Section extends SP_v1_0_8_AdminPageFramework_Form_View___CSS_Base {
    protected function _get() {
        return $this->_getFormSectionRules();
    }
    private function _getFormSectionRules() {
        $_sCSSRules = ".shellpress-section .form-table {margin-top: 0;}.shellpress-section .form-table td label { display: inline;}.shellpress-section-tabs-contents {margin-top: 1em;}.shellpress-section-tabs { margin: 0;}.shellpress-tab-content { padding: 0.5em 2em 1.5em 2em;margin: 0;border-style: solid;border-width: 1px;border-color: #dfdfdf;background-color: #fdfdfd; }.shellpress-section-tab {background-color: transparent;vertical-align: bottom; margin-bottom: -2px;margin-left: 0px;margin-right: 0.5em;background-color: #F1F1F1;font-weight: normal;}.shellpress-section-tab:hover {background-color: #F8F8F8;}.shellpress-section-tab.active {background-color: #fdfdfd; }.shellpress-section-tab h4 {margin: 0;padding: 0.4em 0.8em;font-size: 1.12em;vertical-align: middle;white-space: nowrap;display:inline-block;font-weight: normal;}.shellpress-section-tab.nav-tab {padding: 0.2em 0.4em;}.shellpress-section-tab.nav-tab a {text-decoration: none;color: #464646;vertical-align: inherit; outline: 0; }.shellpress-section-tab.nav-tab a:focus { box-shadow: none;}.shellpress-section-tab.nav-tab.active a {color: #000;}.shellpress-content ul.shellpress-section-tabs > li.shellpress-section-tab {list-style-type: none;margin: -4px 4px -1px 0;}.shellpress-repeatable-section-buttons {float: right;clear: right;margin-top: 1em;}.shellpress-repeatable-section-buttons.disabled > .repeatable-section-button {color: #edd;border-color: #edd;}.shellpress-section-caption {text-align: left;margin: 0;}.shellpress-section .shellpress-section-title {}.shellpress-sections.sortable-section > .shellpress-section {padding: 1em 1.8em 1em 2.6em;}.shellpress-sections.sortable-section > .shellpress-section.is_subsection_collapsible {display: block; float: none;border: 0px;padding: 0;background: transparent;}.shellpress-sections.sortable-section > .shellpress-tab-content {display: block; float: none;border: 0px;padding: 0.5em 2em 1.5em 2em;margin: 0;border-style: solid;border-width: 1px;border-color: #dfdfdf;background-color: #fdfdfd;}.shellpress-sections.sortable-section > .shellpress-section {margin-bottom: 1em;}.shellpress-section {margin-bottom: 1em; }.shellpress-sectionset {margin-bottom: 1em; display:inline-block;width:100%;}.shellpress-section > .shellpress-sectionset {margin-left: 2em;}";
        $_sCSSRules.= $this->___getForWP47();
        return $_sCSSRules;
    }
    private function ___getForWP47() {
        if (version_compare($GLOBALS['wp_version'], '4.7', '<')) {
            return '';
        }
        return ".shellpress-content ul.shellpress-section-tabs > li.shellpress-section-tab {margin-bottom: -2px;}";
    }
}
