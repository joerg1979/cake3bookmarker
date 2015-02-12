<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Collection\Collection;
/**
 * Bookmark Entity.
 */
class Bookmark extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'title' => true,
        'description' => true,
        'url' => true,
        'user' => true,
        'tags' => true,
        'tag_string' => true,
    ];
    protected function _getTagString(){
        if(isset($this->_properties['tag_string'])){
            return $this->_properties['tag_string'];
        }
        if(empty($this->tags)){
            return '';
        }
        $tags = new Collection($this->tags);
        $str = $tags->reduce(function ($string, $tag){
            return $string . $tag->title . ', ';
        },'');
        return trim($str, ', ');
    }
    public function beforeSave($event, $entity, $options){
        if($entity->tag_string){
            $entity->tags = $this->_buildTags($entity->tag_string);
        }
    }
    protected function _buildTags($tagString){
        $new = array_unique(array_map('trim', explode(',', $tagString)));
        $out = [];
        $query = $this->Tags->find()
                ->where(['Tags.title IN' => $new]);
        
        #remove existing tags from list of new tags
        foreach ($query->extract('title') as $existing){
            $index = array_search($existing, $new);
            if($index !== FALSE){
                unset($new[$index]);
            }
        }
        #Add existing tags
        foreach($query as $tag){
            $out[] = $tag;
        }
        #Add new tags
        foreach($new as $tag){
            $out[] = $this->Tags->newEntity(['title' => $tag]);
        }
        return $out;
    }
}
