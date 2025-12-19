<?php
class PostModel {
  private $file = __DIR__ . '/../../storage/posts.json';

  public function all() {
    return json_decode(file_get_contents($this->file), true);
  }
}
