<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use KurtJensen\MyCalendar\Models\Event as MyEvents;

class Event extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.event.name',
            'description' => 'kurtjensen.mycalendar::lang.event.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'kurtjensen.mycalendar::lang.event.slug_title',
                'description' => 'kurtjensen.mycalendar::lang.event.slug_description',
                'default' => '{{ :slug }}',
                'type' => 'string',
            ],
            'linkpage' => [
                'title' => 'kurtjensen.mycalendar::lang.event.link_title',
                'description' => 'kurtjensen.mycalendar::lang.event.link_desc',
                'type' => 'dropdown',
                'group' => 'kurtjensen.mycalendar::lang.event.link_group',
            ],
        ];
    }

    public function getLinkpageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->page['ev'] = $this->loadEvents();
        $this->page['backLink'] = $this->property('linkpage', '');
    }

    public function loadEvents()
    {
        $slug = $this->property('slug');
        if (!$e = MyEvents::where('is_published', true)->find($slug)) {
            return 'Event not found!';
        }

        $maxLen = $this->property('title_max', 100);

        $link = $e->link ? $e->link : '';
        return ['name' => $e->name, 'date' => $e->date, 'time' => $e->human_time, 'link' => $link, 'text' => $e->text];
    }
}
