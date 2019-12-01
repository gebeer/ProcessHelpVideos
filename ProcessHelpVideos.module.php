<?php

namespace ProcessWire;


class ProcessHelpVideos extends Process implements ConfigurableModule
{

    /**
     * Page edit form
     * 
     * @var InputfieldForm
     * 
     */
    protected $form;

    /**
     * Page being edited
     * 
     * @var Page
     * 
     */
    protected $page;

    /**
     * description field id
     * 
     * @var int
     * 
     */
    protected $descriptionField;

    /**
     * description field name
     * 
     * @var string
     * 
     */
    protected $descfieldName;

    /**
     * file field id
     * 
     * @var int
     * 
     */
    protected $fileField;

    /**
     * file field name
     * 
     * @var string
     * 
     */
    protected $filefieldName;

    /**
     * parent template name
     * 
     * @var string
     * 
     */
    protected $parentTemplate;

    /**
     * parent page id
     * 
     * @var int
     * 
     */
    protected $parentPage;

    /**
     * child template name
     * 
     * @var string
     * 
     */
    protected $childTemplate;


    /**
     * This is an optional initialization function called before any execute functions.
     *
     * If you don't need to do any initialization common to every execution of this module,
     * you can simply remove this init() method. 
     *
     */
    public function init()
    {
        // populate properties from module config data
        $moduleConfig = new \ProcessWire\ProcessHelpVideosConfig;
        $data = array_merge($moduleConfig->getDefaults(), $this->data);          // merge with default data
        foreach ($data as $key => $value) $this->$key = $value;       // populate final data to class properties

        $this->addScriptsAndStyles();
        parent::init(); // always remember to call the parent init
    }

    /**
     * This function is executed when a page with your Process assigned is accessed. 
     *
     * This can be seen as your main or index function. You'll probably want to replace
     * everything in this function. 
     *
     */
    public function ___execute()
    {
        $this->headline($this->wire('pages')->get($this->parentPage)->title);
        return $this->renderHelpDashboard();
    }

    /**
     * renders the help video section
     *
     * @return string
     */
    public function renderHelpDashboard()
    {

        return $this->renderItems();
    }

    /**
     * renders help videos
     *
     * @return string
     */
    public function renderItems()
    {
        $helpItems = $this->wire('pages')->find("template={$this->childTemplate}, sort=sort");
        if (!$helpItems->count) {
            $parentPage = $this->wire('pages')->get($this->parentPage);
            $out = "<strong>" . $this->_('There are no help videos yet.') . "</strong><br>";
            $out .=
                sprintf($this->_('To add help videos, add child pages to the page %s in the page tree.'), "<strong>{$parentPage->title}</strong>");
            return $out;
        }
        $out = '<div class="uk-grid-match" uk-grid>';
        foreach ($helpItems as $item) {
            $description = $item->get($this->descriptionField);
            $pageEditlink = ($item->editable) ? "<br><a href='{$item->editUrl}' title='" . $this->_('Edit page') . "'>"
                . $this->_('Edit') . " {$item->title}</a>" : '';
            $video = $this->renderVideo($item, $pageEditlink);
            $out .= "
			<div class='uk-width-1-3'>
				<div class='uk-card uk-card-default uk-card-body uk-flex uk-flex-column uk-flex-between'>
					<h3 class='uk-card-title'>{$item->title}</h3>
					<div class='videocontent'>
						{$description}
                        {$video}
                        {$pageEditlink}
					</div>
				</div>
			</div>";
        }
        $out .= '</div>';

        return $out;
    }

    /**
     * Render video tag
     *
     * @param \ProcessWire\Page $page
     * @param string $editLink
     * @return string
     */
    public function renderVideo($page, $editLink)
    {
        // if ($page->of()) $page->of(false);
        $out = '';
        $source = '';
        foreach ($page->{$this->filefieldName} as $file) {
            $id = $file->created;
            $source .= "<source src='{$file->httpUrl()}' type='video/{$file->ext()}'>";
        }
        if (empty($source)) {
            $pageEditlink = ($page->editable) ? "<a href='{$page->editUrl}' title='Edit page'>{$page->title}</a>" : $page->title;
            $out = '<strong>' . $this->_('No video found.') . '</strong>';
            if (empty($editLink) && $page->editable) $out .= '<br>' . sprintf($this->_('Please upload a video to page %s'), "<br>{$pageEditlink}");
            return $out;
        }
        $vjsOptions = json_encode(array(
            'fluid' => true,
            "liveui" => true
        ));
        $out .= "<video uk-responsive-width id='id-{$id}' class='video-js vjs-big-play-centered' controls preload='auto' data-vjsoptions='{$vjsOptions}'>
            {$source}
            <p class='vjs-no-js'>
                To view this video please enable JavaScript, and consider upgrading to a web browser that
                <a href='http://videojs.com/html5-video-support/' target='_blank'>supports HTML5 video</a>
            </p>
        </video>
        ";
        return $out;
    }

    /**
     * loads scripts and styles
     *
     * @return void
     */
    public function addScriptsAndStyles()
    {

        $this->wire('config')->styles->add("https://vjs.zencdn.net/5.19.2/video-js.css");
        $this->wire('config')->scripts->add("https://vjs.zencdn.net/5.19.2/video.js");
        if ($this->wire('user')->admin_theme != 'AdminThemeUikit') {
            $this->wire('config')->styles->add($this->wire('config')->urls->{$this->className} . 'assets/nouikit.css');
        }
    }

    /**
     * installs all necessary fields
     *
     * @return void
     */
    public function installFields()
    {
        $fields = $this->wire('fields');
        $ckeFields = array();
        $fileFields = array();

        // check for CKEditor fields that we can use for help video template
        foreach ($fields->find('type=FieldtypeTextarea') as $f) {
            if ($f->inputfieldClass != 'InputfieldCKEditor') continue;
            $ckeFields[] = ['id' => $f->id, 'name' => $f->name];
        }

        // check for file fields that we can use for help video template
        foreach ($fields->find('type=FieldtypeFile') as $f) {
            if (strpos($f->extensions, 'mp4') === false) continue;
            $fileFields[] = ['id' => $f->id, 'name' => $f->name];
        }

        // no ckeditor fields found, create one
        if (!count($ckeFields) && !$fields->get($this->descfieldName)) {
            $field = new \ProcessWire\Field;
            $field->type = $this->wire('modules')->get('FieldtypeTextarea');
            $field->name = $this->descfieldName;
            $field->label = "Description";
            $field->inputfield = "InputfieldCKEditor";
            $field->inputfieldClass = "InputfieldCKEditor";
            $field->contentType = 1; // contentTypeHTML
            $fields->save($field);
            $field = $fields->get($this->descfieldName);
            $this->descriptionField = $field->id;
            $this->descfieldName = $field->name;
        } else {
            $this->descriptionField = $ckeFields[0]['id'];
            $this->descfieldName = $ckeFields[0]['name'];
        }

        // no file fields found, create one
        if (!count($fileFields) && !$fields->get($this->filefieldName)) {
            $field = new \ProcessWire\Field;
            $field->type = $this->wire('modules')->get('FieldtypeFile');
            $field->name = $this->filefieldName;
            $field->label = "Video";
            $field->extensions = "mp4 webm";
            $field->inputfield = "InputfieldFile";
            $field->descriptionRows = 0;
            $fields->save($field);
            $field = $fields->get($this->filefieldName);
            $this->fileField = $field->id;
            $this->filefieldName = $field->name;
        } else {
            $this->fileField = $fileFields[0]['id'];
            $this->filefieldName = $fileFields[0]['name'];
        }

        $configData = $this->wire('modules')->getModuleConfigData($this);
        $configData['fileField'] = $this->fileField;
        $configData['filefieldName'] = $this->filefieldName;
        $configData['descriptionField'] = $this->descriptionField;
        $configData['descfieldName'] = $this->descfieldName;
        $this->wire('modules')->saveModuleConfigData($this, $configData);
    }

    /**
     * installs all necessary templates
     * also adjusts template family settings
     *
     * @return void
     */
    public function installTemplates()
    {
        $fields = $this->wire('fields');
        $templates = $this->wire('templates');

        if (!$templates->get($this->parentTemplate)) {
            $fieldgroup = new \ProcessWire\Fieldgroup;
            $fieldgroup->name = $this->parentTemplate;
            $fieldgroup->add($fields->get('title'));
            $fieldgroup->save();
            $template = new \ProcessWire\Template;
            $template->name = $this->parentTemplate;
            $template->setFieldgroup($fieldgroup);
            $parentTemplate = $template->save();
            $this->parentTemplate = $parentTemplate->id;
        }

        if (!$templates->get($this->childTemplate)) {
            bd($fields->get($this->descriptionField));
            $fieldgroup = new \ProcessWire\Fieldgroup;
            $fieldgroup->name = $this->childTemplate;
            $fieldgroup->add($fields->get('title'));
            $fieldgroup->add($fields->get($this->descriptionField));
            $fieldgroup->add($fields->get($this->fileField));
            $fieldgroup->save();
            bd($fieldgroup);
            $template = new \ProcessWire\Template;
            $template->name = $this->childTemplate;
            $template->setFieldgroup($fieldgroup);
            // template family settings
            $template->noChildren = 1;
            $template->parentTemplates = array($templates->get($this->parentTemplate)->id);
            $childTemplate = $template->save();
            $this->childTemplate = $childTemplate->id;
        }

        // template family settings
        $homeTemplate = $templates->get('home');
        // set childTemlates and parentTemplate for parentTemplate
        $parentTemplate->childTemplates = array($templates->get($this->childTemplate)->id);
        $parentTemplate->parentTemplates = array($homeTemplate->id);
        $parentTemplate->save();

        // set parentTemplate as childTemplate for home template if home template has set allowed child page templates
        $childTemplates = $homeTemplate->childTemplates;
        if (count($childTemplates)) {
            array_push($childTemplates, $parentTemplate->id);
            $homeTemplate->childTemplates = $childTemplates;
            $homeTemplate->save();
        }
    }


    /**
     * installs the parent page for holding the help video pages
     *
     * @return void
     */
    public function installVideoParent()
    {
        $home = $this->wire('pages')->get(1);
        $helpvideosParent = new \ProcessWire\Page;
        $helpvideosParent->template = $this->parentTemplate;
        $helpvideosParent->parent = $home;
        $helpvideosParent->title = "Help Videos";
        $helpvideosParent->name = 'processhelpvideos-help';
        $helpvideosParent->addStatus('hidden');
        $helpvideosParent->save();

        $this->parentPage = $helpvideosParent->id;
        $configData = $this->wire('modules')->getModuleConfigData($this);
        $configData['parentPage'] = $helpvideosParent->id;
        $this->wire('modules')->saveModuleConfigData($this, $configData);
    }

    /**
     * Called only when your module is installed
     *
     * If you don't need anything here, you can simply remove this method. 
     *
     */
    public function ___install()
    {
        $moduleConfig = new \ProcessWire\ProcessHelpVideosConfig;
        $defaults = $moduleConfig->getDefaults();
        foreach ($defaults as $key => $value) $this->$key = $value;
        $this->installFields();
        $this->installTemplates();
        $this->installVideoParent();

        parent::___install(); // always remember to call parent method
    }

    /**
     * Called only when your module is uninstalled
     *
     * This should return the site to the same state it was in before the module was installed. 
     *
     * If you don't need anything here, you can simply remove this method. 
     *
     */
    public function ___uninstall()
    {

        parent::___uninstall(); // always remember to call parent method
    }
}
