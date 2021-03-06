<?php

namespace Krlove\EloquentModelGenerator\Processor;

use Illuminate\Database\DatabaseManager;
use Krlove\CodeGenerator\Model\DocBlockModel;
use Krlove\CodeGenerator\Model\PropertyModel;
use Krlove\CodeGenerator\Model\VirtualPropertyModel;
use Krlove\EloquentModelGenerator\Config;
use Krlove\EloquentModelGenerator\Model\EloquentModel;
use Krlove\EloquentModelGenerator\TypeRegistry;

/**
 * Class FieldProcessor
 *
 * @package Krlove\EloquentModelGenerator\Processor
 */
class FieldProcessor implements ProcessorInterface
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var TypeRegistry
     */
    protected $typeRegistry;

    /**
     * FieldProcessor constructor.
     *
     * @param DatabaseManager $databaseManager
     * @param TypeRegistry    $typeRegistry
     */
    public function __construct(DatabaseManager $databaseManager, TypeRegistry $typeRegistry)
    {
        $this->databaseManager = $databaseManager;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        $schemaManager = $this->databaseManager->connection($config->get('connection'))->getDoctrineSchemaManager();
        $prefix = $this->databaseManager->connection($config->get('connection'))->getTablePrefix();

        $tableDetails = $schemaManager->listTableDetails($prefix . $model->getTableName());
        $primaryColumnNames = $tableDetails->getPrimaryKey() ? $tableDetails->getPrimaryKey()->getColumns() : [];

        $columnNames = [];
        foreach ($tableDetails->getColumns() as $column) {


            $keyFormatted = $column->getName();

            $prefix = $config->get('prefix');


            $keyFormatted = str_replace('UF_', '', $column->getName());  // Обрезаем UF_


            if (!empty($prefix)) {
                $keyFormatted = ucwords(strtolower($keyFormatted), '_'); // Делаем нижний регистр и CamelCase
                $keyFormatted = $prefix . $keyFormatted;
            } else {
                $keyFormatted = lcfirst(ucwords(strtolower($keyFormatted), '_')); // Делаем нижний регистр и CamelCase
            }


            $keyFormatted = str_replace('_', '', $keyFormatted);         // Убираем оставшиеся символы "_"


            $model->addProperty(new VirtualPropertyModel(
                $keyFormatted,
                $this->typeRegistry->resolveType($column->getType()->getName())
            ));

            //  if (!in_array($column->getName(), $primaryColumnNames)) {
            $columnNames[] = $keyFormatted;
            $columnNamesForMaps[$keyFormatted] = $column->getName();
            // }
        }

        $aliasableProperty = new PropertyModel('maps');
        $aliasableProperty->setAccess('protected')->setValue($columnNamesForMaps);
        $model->addProperty($aliasableProperty);

        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue($columnNames)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 5;
    }
}
