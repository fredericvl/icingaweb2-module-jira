<?php

namespace Icinga\Module\Jira;

use RuntimeException as RuntimeExceptionAlias;

class IssueUpdate
{
    /** @var RestApi */
    protected $api;

    protected $comments = [];

    protected $fields = [];

    protected $needsToClose = false;
    protected $closeTransitionId;
    protected $closeResolutionName;

    protected $needsToOpen = false;
    protected $openTransitionId;

    /** @var string */
    protected $key;

    public function __construct(RestApi $api, $issueKey)
    {
        $this->api = $api;
        $this->key = $issueKey;
    }

    public function setCustomField($key, $value)
    {
        $this->fields[$key] = $value;

        return $this;
    }

    public function closeIssue($transitionId, $resolutionName)
    {
        $this->needsToClose        = true;
        $this->closeTransitionId   = $transitionId;
        $this->closeResolutionName = $resolutionName
    }

    public function openIssue($transitionId)
    {
        $this->needsToOpen      = true;
        $this->openTransitionId = $transitionId;
    }

    public function addComment($body)
    {
        $this->comments[] = $body;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function needsClosure()
    {
        return $this->needsToClose;
    }

    public function needsOpening()
    {
        return $this->needsToOpen;
    }

    public function toObject()
    {
        $data = (object) [];
        if (! empty($this->comments)) {
            $data->update = (object) ['comment' => []];
            foreach ($this->comments as $body) {
                $data->update->comment[] = (object) ['add' => (object) ['body' => $body]];
            }
        }
        if (! empty($this->fields)) {
            $data->fields = (object) [];
            foreach ($this->fields as $name => $value) {
                $data->fields->$name = $value;
            }
        }
        if ($this->needsToClose) {
            $data->fields->resolution = (object) ['name' => $this->closeResolutionName];
            $data->transition = (object) ['id' => $this->closeTransitionId];
        }
        if ($this->needsToOpen) {
            $data->fields->assignee = (object) ['name' => ''];
            $data->transition = (object) ['id' => $this->openTransitionId];
        }

        if (empty($data)) {
            throw new RuntimeExceptionAlias('Cannot send empty update');
        }
        $this->api->translateNamesToCustomFields($data);

        return $data;
    }
}
