<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Swatches\Model\Swatch;

class ImageSwatch extends AbstractType
{
    const MODULE_IMAGE_PATH = 'storesetup/media/imageswatch/';
    const SWATCH_DIR = '/attribute/swatch';
    const DELIMITER = '::';
    const DIR_MODE = '0777';
    const FILE_MODE = '0777';

    protected $swatchType = Swatch::SWATCH_INPUT_TYPE_VISUAL;
    protected $swatchVisualType = Swatch::SWATCH_TYPE_VISUAL_IMAGE;
    protected $frontend = 'select';
    protected $type = 'int';

    /**
     * @var Reader
     */
    private $moduleReader;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var File
     */
    private $fileIo;
    /**
     * @var WriteFactory
     */
    private $directoryWriter;

    /**
     * ImageSwatch constructor.
     * @param Reader $moduleReader
     * @param DirectoryList $directoryList
     * @param File $fileIo
     * @param WriteFactory $directoryWriter
     */
    public function __construct(
        Reader $moduleReader,
        DirectoryList $directoryList,
        File $fileIo,
        WriteFactory $directoryWriter
    ) {
        parent::__construct();
        $this->moduleReader = $moduleReader;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->directoryWriter = $directoryWriter;
    }

    /**
     * @param $input
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function buildAttributeData($input)
    {
        $input[Swatch::SWATCH_INPUT_TYPE_KEY] = $this->swatchType;

        // Set order field with same id's as main options
        if (isset($input['swatch']['value'])) {
            foreach ($input['swatch']['value'] as $optionId => $value) {
                if (strpos($value[1], self::DELIMITER) !== false) {
                    list($module, $imageName) = explode(self::DELIMITER, $value[1]);

                    $sourceImage = $this->moduleReader->getModuleDir('', $module) . self::MODULE_IMAGE_PATH . $imageName;
                    $imageShards = Uploader::getDispretionPath($imageName);

                    if (file_exists($sourceImage)) {
                        $destDirectory = $this->directoryList->getPath(DirectoryList::MEDIA) . self::SWATCH_DIR;
                        $destDirectory .= $imageShards;
                        $destImage = $destDirectory . '/' . $imageName;

                        try {
                            // Create destination directory
                            $destination = $this->directoryWriter->create($destDirectory);
                            $destination->create();

                            // Copy imagefile to destination location
                            $source = $this->directoryWriter->create('/');
                            $source->copyFile($sourceImage, $destImage);
                        } catch (Exception $ex) {
                            // Error
                        }
                    }
                    $value[1] = $imageShards . '/' . $imageName;
                }

                if (isset($input['option']['converter'][$optionId])) {
                    $origOptionId = $input['option']['converter'][$optionId];
                    $input['swatch']['value'][$origOptionId] = $value[1];
                    unset($input['swatch']['value'][$optionId]);
                } else {
                    $input['swatch']['value'][$optionId] = $value[1];
                }
            }
        }

        $input['optionvisual'] = $input['option'];
        $input['swatchvisual'] = $input['swatch'];
        $input['defaulttext'] = isset($input['default']) ? $input['default'] : [];

        return parent::buildAttributeData($input);
    }
}
