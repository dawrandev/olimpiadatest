<?php

namespace App\Services\admin;

use App\Repositories\Admin\TopicRepository;

class TopicService
{
    public function __construct(protected TopicRepository $topicRepository)
    {
        //
    }

    public function getTopics($subjectId = null, $perPage = 10, $search = null)
    {
        return $this->topicRepository->getAllWithSubject($subjectId, $perPage, $search);
    }


    public function createTopic($data)
    {
        return $this->topicRepository->create($data);
    }

    public function findById($id)
    {
        return $this->topicRepository->getTopic($id);
    }

    public function updateTopic($data, $id)
    {
        return $this->topicRepository->update($data, $id);
    }
}
