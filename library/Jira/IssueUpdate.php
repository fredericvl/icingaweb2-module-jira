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

    protected $needsToOpen = false;

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

    public function closeIssue()
    {
        $this->needsToClose = true;
    }

    public function openIssue()
    {
        $this->needsToOpen = true;
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
            $data->fields->resolution = (object) ['name' => 'Fixed'];
            $data->transition = (object) ['id' => '61'];
        }
        if ($this->needsToOpen) {
            $data->transition = (object) ['id' => '111'];
        }

        if (empty($data)) {
            throw new RuntimeExceptionAlias('Cannot send empty update');
        }
        $this->api->translateNamesToCustomFields($data);

        return $data;
    }
}
