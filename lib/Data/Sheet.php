<?php

namespace lib\Data;

class Sheet {

  /*
   * Sources are the raw columns in data structure.
   */
  protected $sources = array();
  /*
   * Headers should always be printend at top of sheet.
   */
  protected $headers = array();
  /*
   * Rows are... Bah, the rows.
   */
  protected $rows = array();
  /*
   * Custom fields is an array ($id => $callback) whose behavior is dynamic
   */
  protected $customFields = array();


  /*
   * Attach source & optional header
   */
  public function insertSource($id, $header = null)
  {
    if (is_string($id) && array_search($id, $this->sources) === False)
      $this->sources[] = $id;

    $this->insertHeader($id, $header);

    return $this;
  }

  /*
   * Adds header ($source_id => $string) in a specific $header_id
   */
  public function insertHeader($source_id, $string, $headers_id = null)
  {
    if (is_string($string) && $string != '')
      $this->headers[($headers_id===null?'default':$headers_id)][$source_id] = $string;

    return $this;
  }

  /*
   * Inserts custom field
   */
  public function insertCustomField ($id, $callback = null)
  {
    if (is_string($id) && is_callable($callback))
      $this->customFields[$id] = $callback;
    elseif ($callback === null)
      return isset($this->customFields[$id])?$this->customFields[$id]:False;
    else
      return $this;
  }

  /*
   * Inserts row
   */
  public function insertRow (array $row)
  {
    $this->rows[] = $row;
    return $this;
  }

  /*
   * Returns headers ordered by $field_set
   */
  public function getHeaders($headers_id = null, $field_set = null)
  {
    $headers_id = $headers_id===null?'default':$headers_id;
    $arrayHeaders = array();
    if (isset($this->headers[$headers_id]))
      foreach ($field_set as $item)
        $arrayHeaders[] = isset($this->headers[$headers_id][$item])?$this->headers[$headers_id][$item]:'';
    else
      return array();
    return $arrayHeaders;
  }

  /*
   * Returns field set by array with missing fields
   */
  public function getFieldSet(array $field_set, $fixMissingFields = true)
  {
    if ($fixMissingFields)
      foreach ($this->sources as $item)
        if (array_search($item, $field_set) === False)
          $field_set[] = $item;
    return $field_set;
  }


  /*
   * Returns array sheet with headers & order dictated by $field_set
   */
  public function get(array $field_set = null, $headers_id = null)
  {
    $field_set = $field_set === null?$this->sources:$field_set;
    $arraySheet = $headers_id===False?array():[$this->getHeaders($headers_id, $field_set)];
    foreach ($this->rows as $row)
    {
      $_row = array();
      foreach ($field_set as $item)
      {
        $value = array_search($item, $this->sources)!== False && isset($row[$item])?$row[$item]:'';
        if ($this->insertCustomField($item) !== False)
          $value = call_user_func_array($this->insertCustomField($item), [$row]);
        $_row[] = $value;
      }

      $arraySheet[] = $_row;
    }
    return $arraySheet;
  }

  /*
   * Transforms array sheet in CSV
   */
  public function csv(array $arraySheet)
  {
    $sheet = '';
    foreach ($arraySheet as $row)
    {
      foreach ($row as $item)
      {
        $item = str_replace('"', '\\\"', $item);
        $sheet .= '"' . $item . '",';
      }
      $sheet .= PHP_EOL;
    }
    return $sheet;
  }

}
