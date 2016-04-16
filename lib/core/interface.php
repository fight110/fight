<?php

interface Saver {
    public function insert($content);
    public function update($id, $content);
    public function select($id);
    public function delete($id);
}
