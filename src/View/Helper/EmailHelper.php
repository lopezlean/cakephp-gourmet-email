<?php

namespace Gourmet\Email\View\Helper;

use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Cake\View\StringTemplateTrait;


class EmailHelper extends HtmlHelper
{

    protected $_emailConfig = [
        'attributes' => [
            'link' => [
                'target' => '_blank'
            ],
            'image' => [
                'style' => [
                    'display:block'
                ]
            ],
            'para' => [
                'style' => [
                    'margin-left:0',
                    'margin-right:0',
                    'margin-bottom:1em'
                ]
            ],
            'table' => [
                'border' => 0,
                'cellpadding' => 0,
                'cellspacing' => 0,
                'style' => [
                    'border-collapse:collapse',
                    'mso-table-lspace:0pt',
                    'mso-table-rspace:0pt'
                ]
            ],
        ],
        'templates' => [
            'eolhtml' => '<br>',
            'eoltext' => PHP_EOL,
            'table' => '<table{{attrs}}>{{content}}</table>',
            'tablestart' => '<table{{attrs}}>',
            'tableend' => '</table>'
        ]
    ];

    /**
     * Document type definitions
     *
     * @var string[]
     */
    protected $_docTypes = [
        'html4-strict' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        'html4-trans' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
        'html4-frame' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
        'html5' => '<!DOCTYPE html>',
        'xhtml-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        'xhtml-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'xhtml-frame' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        'xhtml11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
    ];

    protected $_emailType;

    
    public function __construct(View $view, array $config = [])
    {
        
        $this->_defaultConfig = Hash::merge($this->_defaultConfig,$this->_emailConfig);
       
       parent::__construct($view,$config);
    }


    public function beforeRenderFile(Event $event, $viewFile)
    {
        preg_match('/email\/(text|html)\//', $viewFile, $match);
        list(, $this->_emailType) = $match;
        $this->_eol = 'text' == $this->_emailType ? PHP_EOL : '<br>';
    }

    public function implementedEvents():array
    {
        return ['View.beforeRenderFile' => 'beforeRenderFile'];
    }

    public function getType()
    {
        return $this->_emailType;
    }

    protected function _eol()
    {
        return $this->getConfig('templates.eol' . $this->getType());
    }

    /**
     * {@inheritdoc}
     */
    public function docType($type = 'xhtml-strict')
    {
        if (isset($this->_docTypes[$type])) {
            return $this->_docTypes[$type];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function image($path, array $options = array()):string
    {
        if ('text' == $this->getType()) {
            return null;
        }
        return parent::image($path, $this->_mergeAttributes($options, $this->getConfig('attributes.image')));
    }

    /**
     * {@inheritdoc}
     */
    public function link($title, $url = null, array $options = array()):string
    {
        $url = Router::url($url, true);

        if ('html' == $this->getType()) {
            return parent::link($title, $url, $this->_mergeAttributes($options, $this->getConfig('attributes.link')));
        }

        if (empty($url)) {
            return $title;
        }

        $options += ['templates' => []];
        $options['templates'] += ['link' => ':title: :url'];
        return Text::insert($options['templates']['link'], compact('title', 'url'));
    }

    /**
     * {@inheritdoc}
     */
    public function media($path, array $options = array()):string
    {
        if ('text' == $this->getType()) {
            return '';
        }

        return parent::media($path, $this->_mergeAttributes($options, $this->getConfig('attributes.media')));
    }

    /**
     * {@inheritdoc}
     */
    public function para($class, $text, array $options = array()):string
    {
        if ('text' == $this->getType()) {
            return $this->_eol() . $this->_eol() . $text . $this->_eol() . $this->_eol();
        }

        return parent::para($class, $text, $this->_mergeAttributes($options, $this->getConfig('attributes.para')));
    }

    /**
     * Creates table.
     *
     * @param string $content
     * @param array $options
     * @return string
     */
    public function table($content, $options = array())
    {
        if ('text' == $this->getType()) {
            return $content;
        }

        if (false === $options) {
            return $this->getConfig('templates.tableend');
        }

        $tag = 'table';
        if (is_null($content)) {
            $tag = 'tablestart';
        }

        $templater = $this->templater();
        return $templater->format('table', [
        'attrs' => $templater->formatAttributes($this->_mergeAttributes($options, $this->getConfig('attributes.table'))),
        'content' => $content
        ]);
    }

    /**
     * Viewport meta.
     *
     * @param mixed $content
     * @return string
     */
    public function viewport($content = null)
    {
        if (empty($content)) {
            $content = 'width=device-width, initial-scale=1.0';
        }
        if (is_array($content)) {
            $content = implode(', ', $content);
        }
        return $this->meta(array('name' => 'viewport', 'content' => $content));
    }

    /**
     * Merge attributes.
     *
     * @param array $attrs Passed attributes.
     * @param array $merge Default attributes.
     * @return array
     */
    protected function _mergeAttributes($attrs, $merge)
    {
        $appendable = array(
        'class' => array('separator' => ' ', 'match' => 'full'),
        'style' => array('separator' => ';', 'match' => 'part'),
        );

        foreach ((array)$merge as $attr => $values) {
            if (
            !array_key_exists($attr, $attrs)
            || empty($attrs[$attr]) && false !== $attrs[$attr]
            ) {
                $attrs[$attr] = $values;
                continue;
            } elseif (!in_array($attr, array_keys($appendable))) {
                continue;
            }

            if (!is_array($attrs[$attr])) {
                $attrs[$attr] = explode($appendable[$attr]['separator'], $attrs[$attr]);
                $implode = true;
            }

            if (!is_array($values)) {
                $values = explode($appendable[$attr]['separator'], $attrs[$attr]);
            }

            switch ($appendable[$attr]['match']) {

                case 'full':
                    foreach ($values as $value) {
                        if (!in_array($value, $attrs[$attr])) {
                            $attrs[$attr][] = $value;
                        }
                    }
                    break;

                case 'part':
                    foreach ($attrs[$attr] as $k => $haystack) {
                        if (empty($haystack)) {
                            unset($attrs[$attr][$k]);
                            continue;
                        }

                        if (false === strpos($haystack, $appendable[$attr]['separator'])) {
                            $attrs[$attr][$k] = $haystack . $appendable[$attr]['separator'];
                        }

                        foreach ($values as $n => $value) {
                            $needle = current(explode(':', $value)) . ':';
                            if (0 === strpos($haystack, $needle)
                                || false !== strpos($haystack, ';' . $needle)
                                || false !== strpos($haystack, '; ' . $needle)
                            ) {
                                unset($values[$n]);
                            }
                        }
                    }

                    foreach (array_keys($values) as $key) {
                        if (false === strpos($values[$key], $appendable[$attr]['separator'])) {
                            $values[$key] = $values[$key] . $appendable[$attr]['separator'];
                        }
                        $attrs[$attr][] = $values[$key];
                    }
                    break;

                default:
            }

            if (isset($implode)) {
                $attrs[$attr] = implode(' ', $attrs[$attr]);
            }
        }

        return $attrs;
    }
}
