<?php

namespace Levonliu\Packages\Http\Traits;

trait RequestTraits
{

  protected $parameters = [];

  /**
   * 编码参数
   * @return string
   */
  protected function encodeParameters()
  {
    return json_encode($this->parameters, JSON_UNESCAPED_UNICODE);
  }

  /**
   * @param $key
   * @param $value
   * @return $this
   */
  protected function buildParameter($key, $value)
  {
    if (!is_null($key) && !is_null($value)) {
      $this->parameters[$key] = $value;
    }
    return $this;
  }

  protected function clearAllParameter()
  {
    $this->parameters = [];
    return $this;
  }

  protected function addParameters($params)
  {
    if (count($params) > 0) {
      $this->parameters = array_merge($this->parameters, $params);
    }
    return $this;
  }
}
