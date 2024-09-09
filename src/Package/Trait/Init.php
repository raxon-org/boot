<?php
namespace Package\Raxon\Org\Boot\Trait;

use Raxon\Org\App;

use Raxon\Org\Config;
use Raxon\Org\Module\Core;
use Raxon\Org\Module\Dir;
use Raxon\Org\Module\File;

use Raxon\Org\Node\Model\Node;

use Exception;

use Raxon\Org\Exception\FileWriteException;
use Raxon\Org\Exception\ObjectException;

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
        $url_package = $object->config('project.dir.vendor') . 'raxon_org/boot/Data/Package.json';
        $class = File::basename($url_package, $object->config('extension.json'));
        $packages = $object->data_read($url_package);
        $node = new Node($object);
        $is_install = false;
        if($packages){
            foreach($packages->data($class) as $nr => $package){
                $record_options = [
                    'where' => [
                        [
                            'value' => $package,
                            'attribute' => 'name',
                            'operator' => '===',
                        ]
                    ],
                    'process' => true
                ];
                $response = $node->record(
                    'System.Installation',
                    $node->role_system(),
                    $record_options
                );
                $command_options = App::options($object, 'command');
                if(property_exists($options, 'force')){
                    $command = Core::binary($object) . ' install ' . $package;
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
                }
                elseif(!$response){
                    $command = Core::binary($object) . ' install ' . $package;
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
                } else {
                    echo 'Skipping ' . $package . ' installation...' . PHP_EOL;
                }
            }
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
                    $command = Core::binary($object) . ' raxon_org/config framework environment '. $environment . ' -enable-file-permission';
                    Core::execute($object, $command, $output, $notification);
                    if(!empty($output)){
                        echo rtrim($output, PHP_EOL) . PHP_EOL;
                    }
                    if(!empty($notification)){
                        echo rtrim($notification, PHP_EOL) . PHP_EOL;
                    }
                    break;
                default:
                    $command = Core::binary($object) . ' raxon_org/config framework environment '. $environment;
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