<?php

namespace ProcessWire;

/**
 * Configure ProcessHelpVideos module
 *
 * This type of configuration method requires ProcessWire 2.5.5 or newer.
 * For backwards compatibility with older versions of PW, you'll want to
 * instead want to look into the getModuleConfigInputfields() method, which
 * is specified with the .module file. So we are assuming you only need to
 * support PW 2.5.5 or newer here. 
 *
 * For more about configuration methods, see here: 
 * http://processwire.com/blog/posts/new-module-configuration-options/
 *
 * 
 */

class ProcessHelpVideosConfig extends ModuleConfig
{

    public function getDefaults()
    {
        return array(
            'descriptionField' => ($this->descriptionField) ? $this->descriptionField : 0,
            'descfieldName' => ($this->descfieldName) ? $this->descfieldName : 'processhelpvideos_description',
            'fileField' => ($this->fileField) ? $this->fileField : 0,
            'filefieldName' => ($this->filefieldName) ? $this->filefieldName : 'processhelpvideos_video',
            'parentPage' => ($this->parentPage) ? $this->parentPage : 0,
            'parentTemplate' => 'processhelpvideos-help',
            'childTemplate' => 'processhelpvideos-help-item'
        );
    }

    public function getInputfields()
    {
        $inputfields = parent::getInputfields();
        // bd($this->descriptionField);

        // $f = $this->modules->get('InputfieldRadios');
        // $f->attr('name', 'descriptionField');
        // $f->label = $this->_('Choose a field to use for storing help descriptions');
        // if(count($this->ckeFields)) :
        // 	$f->options = $this->ckeFields;
        // else :
        //     $f->options = array(0 => "There is no CKE Editor field for video descriptions in the system. This module will install a file field named {$this->descfieldName}");
        // endif;
        // $f->value = 0;
        // $f->optionColumns = 1;
        // $f->required = true;
        // $inputfields->add($f);

        // $f = $this->modules->get('InputfieldRadios');
        // $f->attr('name', 'fileField');
        // $f->label = $this->_('Choose a field to use for storing help videos');
        // if(count($this->fileFields)) :
        // 	$f->options = $this->fileFields;
        // else :
        //     $f->options = array(0 => "There is no file field for video files in the system. This module will install a file field named {$this->filefieldName}");
        // endif;
        // 	$f->value = 0;
        // 	$f->optionColumns = 1;
        // 	$f->required = true;
        //     $inputfields->add($f);

        $f = $this->modules->get('InputfieldMarkup');
        $f->attr('name', 'description');
        $f->label = $this->_('Fields, templates and pages used/installed by this module');
        $markup = "<p>This module will use following fields and templates and one page. This page's children will hold the help videos.</p>
            <strong>Fields used:</strong><ul>";
        $markup .= "<li>CKEditor field, name: '{$this->descfieldName}'";
        $markup .= ($this->descfieldName == 'processhelpvideos_description') ? ', <em>installed by this module</em>' : ', <em>field was already in system</em>';
        $markup .= '</li>';
        $markup .= "<li>File field, name: '{$this->filefieldName}'";
        $markup .= ($this->filefieldName == 'processhelpvideos_video') ? ', <em>installed by this module</em>' : ', <em>field was already in system</em>';
        $markup .= '</li>';
        $markup .= "</ul>";
        $markup .= "<strong>Templates installed:</strong><ul>
            <li>Template for parent help videos page, name: '{$this->parentTemplate}'</li>
            <li>Template for help videos pages, name: '{$this->childTemplate}'</li></ul>";
        $parentPage = $this->wire('pages')->get($this->parentPage);
        $parentpageTitle = ($parentPage && $parentPage->id) ? $parentPage->title : 'Help Videos';
        $parentpageName = ($parentPage && $parentPage->id) ? $parentPage->name : 'processhelpvideos-help';
        $markup .= "<strong>Parent Page for help videos (hidden child page of homepage):</strong><ul>
            <li>Title: {$parentpageTitle}</li>
            <li>Name: {$parentpageName}</li></ul>
            <em>You may change title and name of this page if you like</em>";
        $f->value = $markup;
        $inputfields->add($f);


        return $inputfields;
    }
}
