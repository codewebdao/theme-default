<?php
namespace System\Core;

abstract class BaseBlock
{   
    protected $name; // Block slug name
    protected $label; // Block name
    protected $props = []; // Block properties

    // Return block name, e.g. "HeaderBlock"
    protected function getName(){
        return ucfirst($this->name);
    }
    protected function setName($value){
        $this->name = $value;
    }
    protected function getLabel(){
        return $this->label;
    }
    protected function setLabel($value){
        $this->label = $value;
    }
    public function setProps(array $props) {
        $this->props = array_merge($this->props, $props);
        return $this;
    }
    protected function getProps() {
        return $this->props;
    }

    // handle data to format that layout file needs
    abstract public function handleData();

    public function render() {
    $layout = !empty($this->props['layout']) ? $this->props['layout'] : 'default';
    // Ensure block name is compatible with operating system path format
    $blockNamePath = str_replace('\\', DIRECTORY_SEPARATOR, $this->getName());
    
    $themeBlockPath = APP_THEME_PATH
        . 'Blocks' . DIRECTORY_SEPARATOR 
        . $blockNamePath . DIRECTORY_SEPARATOR 
        . $layout . '.php';

    if (!file_exists($themeBlockPath)) {
        $themeBlockPath = PATH_APP 
            . 'Blocks' . DIRECTORY_SEPARATOR 
            . $blockNamePath . DIRECTORY_SEPARATOR 
            . 'Views' . DIRECTORY_SEPARATOR 
            . $layout . '.php';
    }
    if(file_exists($themeBlockPath)) {
        $data = $this->handleData();
        $___block_start_time = microtime(true);
        if(is_array($data)) {
            extract($data);
            include $themeBlockPath;
        } else {
            echo "Data of Block ". $this->getName() . " is not array!";
        }
        $___block_duration_ms = (microtime(true) - $___block_start_time) * 1000;
        if (APP_DEBUGBAR) {
            \System\Libraries\Render\View::trackForDebugbar(
                'block',
                $this->getName() . '/' . $layout,
                $themeBlockPath,
                is_array($data) ? $data : [],
                $___block_duration_ms
            );
        }
    } else {
        echo "File Layout: {$themeBlockPath} of Block ". $this->getName() . " not found!";
    }
}

public function json() {
    $layout = !empty($this->props['layout']) ? $this->props['layout'] : 'default';
    // Convert block name to be compatible with system path
    $blockNamePath = str_replace('\\', DIRECTORY_SEPARATOR, $this->getName());
    
    $themeBlockPath = APP_THEME_PATH
        . 'Blocks' . DIRECTORY_SEPARATOR 
        . $blockNamePath . DIRECTORY_SEPARATOR 
        . $layout . '.php';

    if (!file_exists($themeBlockPath)) {
        $themeBlockPath = PATH_APP 
            . 'Blocks' . DIRECTORY_SEPARATOR 
            . $blockNamePath . DIRECTORY_SEPARATOR 
            . 'Views' . DIRECTORY_SEPARATOR 
            . $layout . '.php';
    }
    if(file_exists($themeBlockPath)) {
        $data = $this->handleData();
        $___block_start_time = microtime(true);
        extract($data);
        ob_start();
        include $themeBlockPath;
        $preview_html = ob_get_clean();
        $___block_duration_ms = (microtime(true) - $___block_start_time) * 1000;
        if (APP_DEBUGBAR) {
            \System\Libraries\Render\View::trackForDebugbar(
                'block',
                $this->getName() . '/' . $layout,
                $themeBlockPath,
                array_merge(is_array($data) ? $data : [], [
                    'duration_ms' => round($___block_duration_ms, 2)
                ]),
                $___block_duration_ms
            );
        }
    } else {
        $preview_html = "File Layout: {$layout}.php of Block ". $this->getName() . " not found!";
    }
    return [
        'name'    => $this->getName(),
        'label'   => $this->getLabel(),
        'props'   => $this->getProps(),
        'preview' => $preview_html
    ];
}

}
