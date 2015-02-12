<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Bookmarks Controller
 *
 * @property \App\Model\Table\BookmarksTable $Bookmarks
 */
class BookmarksController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'conditions' => [
                'Bookmarks.user_id' => $this->Auth->user('id'),
            ]
        ];
        $this->set('bookmarks', $this->paginate($this->Bookmarks));
    //    $this->set('_serialize', ['bookmarks']);
    }

    /**
     * View method
     *
     * @param string|null $id Bookmark id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Users', 'Tags', 'BookmarksTags']
        ]);
        $this->set('bookmark', $bookmark);
        $this->set('_serialize', ['bookmark']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $bookmark = $this->Bookmarks->newEntity($this->request->data);
        $bookmark->user_is = $this->Auth->user('id');
        if ($this->request->is('post')) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->data);
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success('The bookmark has been saved.');
                return $this->redirect(['action' => 'index']);
            } 
            $this->Flash->error('The bookmark could not be saved. Please, try again.');
        }
        
      //  $users = $this->Bookmarks->Users->find('list', ['limit' => 200]);
        $tags = $this->Bookmarks->Tags->find('list'); // ['limit' => 200]);
        $this->set(compact('bookmark', 'tags'));
      //  $this->set('_serialize', ['bookmark']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Bookmark id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $bookmark = $this->Bookmarks->get($id, [
            'contain' => ['Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $bookmark = $this->Bookmarks->patchEntity($bookmark, $this->request->data);
            if ($this->Bookmarks->save($bookmark)) {
                $this->Flash->success('The bookmark has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('The bookmark could not be saved. Please, try again.');
            
        }
    //    $users = $this->Bookmarks->Users->find('list', ['limit' => 200]);
        $tags = $this->Bookmarks->Tags->find('list'); //, ['limit' => 200]);
        $this->set(compact('bookmark', 'tags'));
    //    $this->set('_serialize', ['bookmark']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Bookmark id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $bookmark = $this->Bookmarks->get($id);
        if ($this->Bookmarks->delete($bookmark)) {
            $this->Flash->success('The bookmark has been deleted.');
        } else {
            $this->Flash->error('The bookmark could not be deleted. Please, try again.');
        }
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Tagged method
     * Implementin new route for /tagged/* 
     */
    public function tags(){
        $tags = $this->request->params['pass'];
        $bookmarks = $this->Bookmarks->find('tagged',['tags' => $tags]);
        $this->set(compact('bookmarks','tags'));
    }
    
    public function isAuthorized($user){
        $action = $this->request->params['action'];
        #add and index actions are always allowed.
        if(in_array($action, ['index','add','tags'])){
            return TRUE;
        }
        #All other actions require an ID
        if(empty($this->request->params['pass'][0])){
            return FALSE;
        }
        
        $id = $this->request->params['pass'][0];
        $bookmark = $this->Bookmarks->get($id);
        if($bookmark->user_id == $user['id']){
            return TRUE;
        }
        return parent::isAuthorized($user);
    }
            
}
