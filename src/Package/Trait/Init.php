<?php
namespace Package\Raxon\Boot\Trait;

use Raxon\App;

use Raxon\Config;
use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Node\Module\Node;

use Exception;

use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;

trait Init {

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws Exception
     */
    public function installation (): void
    {
        Core::interactive();
        $object = $this->object();
        $options = App::options($object);
        $is_install = false;
        $is_release = false;
        $in_release = false;
        $dir_application = $object->config('project.dir.root');
        $file_release = $dir_application . '.release';
        if(File::exist($file_release)){
            $in_release = true;
        }
        if(property_exists($options, 'lock') && $options->lock === 'release'){
            $dir = new Dir();
            $read = $dir->read($object->config('project.dir.data') . 'Lock' . $object->config('ds'));
            if($read){
                foreach($read as $file){
                    File::delete($file->url);
                }
            }
            $is_release = true;
        }
        $url_installed =
            $object->config('project.dir.node') .
            'Data' .
            $object->config('ds') .
            'System.Installation' .
            $object->config('extension.json')
        ;
        $installed = null;
        if(File::exist($url_installed)){
            $installed = $object->data_read($url_installed);
        }
        $count = 0;
        if($installed){
            foreach($installed->data('System.Installation') as $response){
                $command_options = App::options($object, '#command');
                $name = $response->name ?? null;
                if($name === null){
                    continue;
                }
                if(
                    in_array($name, [
                        "raxon/git"
                    ],
                true
                    )
                ){
                    continue;
                }

                if(property_exists($options, 'force')){
                    $command = Core::binary($object) . ' install ' . $name;
                    if(!empty($command_options)){
                        $command = $command . ' ' . implode(' ', $command_options);
                    }
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    $is_install = true;
                    $count++;
                }
                elseif(
                    $is_release &&
                    !$in_release
                ){
                    $command = Core::binary($object) . ' install ' . $name . ' -patch ';
                    if(!empty($command_options)){
                        $command = $command . ' ' . implode(' ', $command_options);
                    }
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    $is_install = true;
                    $count++;
                }
                elseif(!$in_release){
                    $command = Core::binary($object) . ' install ' . $name . ' -patch ';
                    if(!empty($command_options)){
                        $command = $command . ' ' . implode(' ', $command_options);
                    }
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    $is_install = true;
                    $count++;
                }
                else {
                    echo 'Skipping ' . $name . ' installation...' . PHP_EOL;
                }
            }
            File::touch($file_release);
            echo 'Installed ' . $count . ' packages.' . PHP_EOL;
        }
        if($is_install){
            Config::configure($object);
            $environment = $object->config('framework.environment');
            if(empty($environment)) {
                $environment = Config::MODE_DEVELOPMENT;
            }
            switch($environment){
                case Config::MODE_INIT:
                case Config::MODE_DEVELOPMENT:
                    $environment = Config::MODE_DEVELOPMENT;
                    $command = Core::binary($object) . ' raxon/config framework environment '. $environment . ' -enable-file-permission';
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    break;
                default:
                    $command = Core::binary($object) . ' raxon/config framework environment '. $environment;
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    break;
            }
        }
    }
}