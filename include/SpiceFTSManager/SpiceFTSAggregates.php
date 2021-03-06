<?php
namespace SpiceCRM\includes\SpiceFTSManager;

/**
 * Class SpiceFTSAggregates
 * @package SpiceCRM\includes\SpiceFTSManager
 *
 * handles the aggregates for teh FTS Queries to Elastic
 */
class SpiceFTSAggregates
{

    var $aggregateFields = array();
    var $aggregatesFilters = array();

    function __construct($indexProperties, $aggregatesFilters, $indexSettings = [])
    {
        foreach ($indexProperties as $indexProperty) {
            if ($indexProperty['search']) {
                $searchFields[] = $indexProperty['indexfieldname'];
            }

            if (!empty($indexProperty['aggregate'])) {

                // $this->aggregateFields[$indexProperty['fieldname']] = array(
                $this->aggregateFields[str_replace('->', '-', $indexProperty['indexfieldname'])] = array(
                    'indexfieldname' => $indexProperty['indexfieldname'],
                    'fieldname' => $indexProperty['fieldname'],
                    'fielddetails' => SpiceFTSUtils::getDetailsForField($indexProperty['path']),
                    'field' => $indexProperty['indexfieldname'] . '.agg',
                    'name' => $indexProperty['name'],
                    'type' => $indexProperty['aggregate'],
                    'aggregatesize' => $indexProperty['aggregatesize'],
                    'aggregatepriority' => $indexProperty['aggregatepriority'],
                    'metadata' => SpiceFTSUtils::getFieldIndexParams(null, $indexProperty['path'])
                );

                // check if we have aggParams
                if ($indexProperty['aggregateaddparams']) {
                    $addParamsSting = html_entity_decode(base64_decode($indexProperty['aggregateaddparams']));
                    $addParamsSting = str_replace('$field', $indexProperty['indexfieldname'] . '.agg', $addParamsSting);
                    $this->aggregateFields[str_replace('->', '-', $indexProperty['indexfieldname'])]['aggParams'] = json_decode($addParamsSting, true);
                }
            }
        }



        $this->aggregatesFilters = $aggregatesFilters;
    }

    function buildQueryFilterFromAggregates()
    {
        $postFilter = array();
        foreach ($this->aggregatesFilters as $aggregatesFilter => $aggregatesFilterValues) {
            $postFilter['bool']['must'][] = SpiceFTSFilters::buildFiltersFromAggregate($this->aggregateFields[$aggregatesFilter]['indexfieldname'] ?: $aggregatesFilter, $aggregatesFilterValues);
        }

        return count($postFilter) > 0 ? $postFilter : false;
    }

    function buildAggFilters(){
        $aggFilters = [];
        foreach ($this->aggregatesFilters as $aggregatesFilter => $aggregatesFilterValues) {
                $aggFilters['bool']['must'][] = SpiceFTSFilters::buildFiltersFromAggregate($this->aggregateFields[$aggregatesFilter]['indexfieldname'], $aggregatesFilterValues);
        }
        return $aggFilters;
    }

    function buildAggregates()
    {
        $aggs = array();
        foreach ($this->aggregateFields as $aggregateField => $aggregateIndexFieldData) {
            // go over all aggregate filters passed in and see if one is applicable to be added
            $aggFilters = array();

            $aggregateName = str_replace('->', '-', $aggregateIndexFieldData['indexfieldname']);

            foreach ($this->aggregatesFilters as $aggregatesFilter => $aggregatesFilterValues) {
                if ($aggregatesFilter != $aggregateIndexFieldData['indexfieldname'] && $aggregatesFilter != $aggregateField) {
                    $aggFilters['bool']['must'][] = SpiceFTSFilters::buildFiltersFromAggregate($this->aggregateFields[$aggregatesFilter]['indexfieldname'], $aggregatesFilterValues);
                }
            }

            // if we have a filter for the aggregation pass it in
            switch ($aggregateIndexFieldData['type']) {
                case 'datem':
                    $aggParams = array('date_histogram' => array(
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg',
                        "interval" => "month",
                        "format" => 'MM/yyyy'
                    ));
                    break;
                case 'datew':
                    $aggParams = array('date_histogram' => array(
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg',
                        "interval" => "week",
                        "format" => 'w/yyyy'
                    ));
                    break;
                case 'dateq':
                    $aggParams = array('date_histogram' => array(
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg',
                        "interval" => "quarter",
                        "format" => 'MM/yyyy'
                    ));
                    break;
                case 'datey':
                    $aggParams = array('date_histogram' => array(
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg',
                        "interval" => "year",
                        "format" => 'yyyy'
                    ));
                    break;
                case 'term':
                    $aggParams = array('terms' => array(
                        'size' => isset($aggregateIndexFieldData['aggregatesize']) ? $aggregateIndexFieldData['aggregatesize'] : 10,
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg'
                    ));
                    break;
                case 'range':
                    if (isset($aggregateIndexFieldData['aggParams']))
                        $aggParams = $aggregateIndexFieldData['aggParams'];
                    break;
                default:
                    $aggParams = array('terms' => array(
                        'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg'
                    ));
                    break;
            }

            if (count($aggFilters) > 0) {
                $aggs{$aggregateName} = array(
                    'filter' => $aggFilters,
                    'aggs' => array(
                        $aggregateName => $aggParams
                    )
                );
            } else {
                $aggs{$aggregateName} = $aggParams;
            }
        }

        // add tags
        $tagAggregator = [
            'terms' => [
                'field' => 'tags.agg',
                'size' => 25
            ]
        ];

        $aggFilters = array();
        foreach ($this->aggregatesFilters as $aggregatesFilter => $aggregatesFilterValues) {
            if ($aggregatesFilter != 'tags') {
                $aggFilters['bool']['must'][] = SpiceFTSFilters::buildFiltersFromAggregate('tags', $aggregatesFilterValues);
            }
        }
        if (count($aggFilters) > 0) {
            $aggs{'tags'} = array(
                'filter' => $aggFilters,
                'aggs' => array(
                    'tags' => $tagAggregator
                )
            );
        } else {
            $aggs{'tags'} = $tagAggregator;
        }

        // add the field info so we can later on enrich thje reponse
        /*
        $aggregates[$aggregateName] = array(
            'field' => $aggregateIndexFieldData['indexfieldname'] . '.agg',
            'name' => $aggregateIndexFieldData['name']
        );
        */

        return count($aggs) > 0 ? $aggs : false;

    }

    function processAggregations(&$aggregations)
    {
        $appListStrings = return_app_list_strings_language($GLOBALS['current_language']);

        foreach ($aggregations as $aggField => $aggData) {

            $aggregations[$aggField]['aggregateindex'] = $aggField;
            $aggregations[$aggField]['aggregatepriority'] = $this->aggregateFields[$aggField]['aggregatepriority'];;
            $aggregations[$aggField]['fieldname'] = $this->aggregateFields[$aggField]['fieldname'];
            $aggregations[$aggField]['fielddetails'] = $this->aggregateFields[$aggField]['fielddetails'];
            $aggregations[$aggField]['name'] = $this->aggregateFields[$aggField]['name'];
            $aggregations[$aggField]['type'] = $this->aggregateFields[$aggField]['type'];

            $buckets = $aggregations[$aggField]['buckets'] ?: $aggregations[$aggField][$aggField]['buckets'];

            foreach ($buckets as $aggItemIndex => &$aggItemData) {


                if($aggItemData['doc_count'] == 0) {
                    unset($buckets[$aggItemIndex]);
                    //continue;
                }


                switch ($this->aggregateFields[$aggField]['type']) {
                    case 'datew':
                    case 'datem':
                    $aggItemData['displayName'] = $aggItemData['key_as_string'];
                        $keyArr = explode('/', $aggItemData['key_as_string']);
                        $fromDate = new \DateTime($keyArr[1] . '-' . $keyArr[0] . '-01 00:00:00');
                        $aggItemData['from'] = $fromDate->format('Y-m-d') . ' 00:00:00';
                        $aggItemData['to'] = $fromDate->format('Y-m-t') . ' 23:59:59';
                    $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                        break;
                    case 'datey':
                        $aggItemData['displayName'] = $aggItemData['key_as_string'];
                        $aggItemData['from'] = $aggItemData['key_as_string'] . '-01-01 00:00:00';
                        $aggItemData['to'] = $aggItemData['key_as_string'] . '-12-31 23:59:59';
                        $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                        break;
                    case 'dateq':
                        $dateArray = explode('/', $aggItemData['key_as_string']);
                        $aggItemData['displayName'] = 'Q' . ceil($dateArray[0] / 3) . '/' . $dateArray[1];

                        switch (ceil($dateArray[0] / 3)) {
                            case 1:
                                $aggItemData['from'] = $dateArray[1] . '-01-01 00:00:00';
                                $aggItemData['to'] = $dateArray[1] . '-03-31 23:59:59';
                                break;
                            case 2:
                                $aggItemData['from'] = $dateArray[1] . '-04-01 00:00:00';
                                $aggItemData['to'] = $dateArray[1] . '-06-30 23:59:59';
                                break;
                            case 3:
                                $aggItemData['from'] = $dateArray[1] . '-07-01 00:00:00';
                                $aggItemData['to'] = $dateArray[1] . '-09-30 23:59:59';
                                break;
                            case 4:
                                $aggItemData['from'] = $dateArray[1] . '-10-01 00:00:00';
                                $aggItemData['to'] = $dateArray[1] . '-12-31 23:59:59';
                                break;
                        }
                        $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                        break;
                    default:
                        switch ($this->aggregateFields[$aggField]['metadata']['type']) {
                            case 'multienum':
                            case 'enum':
                                $aggItemData['displayName'] = $appListStrings[$this->aggregateFields[$aggField]['metadata']['options']][$aggItemData['key']] ?: $aggItemData['key'];
                                $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                                break;
                            case 'bool':
                                $aggItemData['displayName'] = $appListStrings['dom_int_bool'][$aggItemData['key']];
                                $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                                break;
                            default:
                                $aggItemData['displayName'] = $aggItemData['key'];
                                $aggItemData['aggdata'] = $this->getAggItemData($aggItemData);
                                break;
                        }
                        break;
                }

                // see if we need to check the box
                /*
                foreach ($this->aggregatesFilters[$aggField] as $aggregatesFilterValue) {
                    $filterData = json_decode(html_entity_decode(base64_decode($aggregatesFilterValue)), true);
                    if ($aggItemData['key'] == $filterData['key'])
                        $aggregations[$aggField]['buckets'][$aggItemIndex]['checked'] = true;
                }
                */
            }

            // nasty trick to get this array reshuffled - if not seuenced angular will not iterate over it
            // ToDo: find a nice way to handle this
            $aggregations[$aggField]['buckets'] = array_merge($buckets, []);
        }
        return $aggregations;
    }

    private function getAggItemData($aggItemData){
        $aggData = array();

        foreach($aggItemData as $key => $value){
            if($key !== 'doc_count'){
                $aggData[$key] = $value;
            }
        }
        return base64_encode(json_encode($aggData));
    }
}