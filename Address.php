<?php
class Address {
  // -----------------------------
  // Private instance attributes
  // -----------------------------
  private $name    = "";
  private $street  = "";
  private $city    = "";
  private $state   = "";
  private $country = "";
  private $zip     = "";

  // -----------------------------
  // Constructor
  // -----------------------------
  public function __construct($name = "", $street = "", $city = "", $state = "", $country = "", $zip = "") {
    $this->name($name);
    $this->street($street);
    $this->city($city);
    $this->state($state);
    $this->country($country);
    $this->zip($zip);
  }

  // -----------------------------
  // Getters & Setters
  // -----------------------------
  public function name($v = null) {
    if ($v === null) return $this->name;
    $this->name = $v;
    return $this;
  }

  public function street($v = null) {
    if ($v === null) return $this->street;
    $this->street = $v;
    return $this;
  }

  public function city($v = null) {
    if ($v === null) return $this->city;
    $this->city = $v;
    return $this;
  }

  public function state($v = null) {
    if ($v === null) return $this->state;
    $this->state = $v;
    return $this;
  }

  public function country($v = null) {
    if ($v === null) return $this->country;
    $this->country = $v;
    return $this;
  }

  public function zip($v = null) {
    if ($v === null) return $this->zip;
    $this->zip = $v;
    return $this;
  }

  // -----------------------------
  // String representations
  // -----------------------------
  public function __toString() {
    return $this->name . ", " .
           $this->street . ", " .
           $this->city . ", " .
           $this->state . ", " .
           $this->country . ", " .
           $this->zip;
  }

  // -----------------------------
  // Tab Seperated Values serialization
  // -----------------------------
  public function toTSV() {
    // One record per line, tab-separated
    return $this->name . "\t" .
           $this->street . "\t" .
           $this->city . "\t" .
           $this->state . "\t" .
           $this->country . "\t" .
           $this->zip . "\n";
  }

  public function fromTSV($tsvLine) {
    // Parse one TSV line into object fields
    $tsvLine = trim($tsvLine);
    $parts = explode("\t", $tsvLine);

    //allow missing fields
    $this->name(isset($parts[0]) ? $parts[0] : "");
    $this->street(isset($parts[1]) ? $parts[1] : "");
    $this->city(isset($parts[2]) ? $parts[2] : "");
    $this->state(isset($parts[3]) ? $parts[3] : "");
    $this->country(isset($parts[4]) ? $parts[4] : "");
    $this->zip(isset($parts[5]) ? $parts[5] : "");

    return $this;
  }
}
?>
