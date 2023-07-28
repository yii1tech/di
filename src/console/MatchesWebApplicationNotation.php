<?php

namespace yii1tech\di\console;

use CException;
use Yii;

/**
 * MatchesWebApplicationNotation adds those methods to console application, which are declared only at {@see \CWebApplication}.
 *
 * In particular this allows usage of widgets and view rendering inside console application.
 *
 * @mixin \CConsoleApplication
 *
 * @property \IAuthManager $authManager The authorization manager component.
 * @property \CAssetManager $assetManager The asset manager component.
 * @property \CHttpSession $session The session component.
 * @property \CWebUser $user The user session information.
 * @property \IViewRenderer $viewRenderer The view renderer.
 * @property \CClientScript $clientScript The client script manager.
 * @property \IWidgetFactory $widgetFactory The widget factory.
 * @property \CThemeManager $themeManager The theme manager.
 * @property \CTheme $theme The theme used currently. Null if no theme is being used.
 * @property string $viewPath The root directory of view files. Defaults to 'protected/views'.
 * @property string $systemViewPath The root directory of system view files. Defaults to 'protected/views/system'.
 * @property string $layoutPath The root directory of layout files. Defaults to 'protected/views/layouts'.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait MatchesWebApplicationNotation
{
    /**
     * @var \CTheme|string current theme.
     */
    private $_theme;
    /**
     * @var string view path.
     */
    private $_viewPath;
    /**
     * @var string system view path.
     */
    private $_systemViewPath;
    /**
     * @var string layout view path.
     */
    private $_layoutPath;

    /**
     * @return \IAuthManager the authorization manager component
     */
    public function getAuthManager()
    {
        return $this->getComponent('authManager');
    }

    /**
     * @return \CAssetManager the asset manager component
     */
    public function getAssetManager()
    {
        return $this->getComponent('assetManager');
    }

    /**
     * @return \CHttpSession the session component
     */
    public function getSession()
    {
        return $this->getComponent('session');
    }

    /**
     * @return \CWebUser the user session information
     */
    public function getUser()
    {
        return $this->getComponent('user');
    }

    /**
     * Returns the view renderer.
     * If this component is registered and enabled, the default view rendering logic defined
     * in {@see \CBaseController} will be replaced by this renderer.
     *
     * @return \IViewRenderer the view renderer.
     */
    public function getViewRenderer()
    {
        return $this->getComponent('viewRenderer');
    }

    /**
     * Returns the client script manager.
     *
     * @return \CClientScript the client script manager
     */
    public function getClientScript()
    {
        return $this->getComponent('clientScript');
    }

    /**
     * Returns the widget factory.
     *
     * @return \IWidgetFactory the widget factory
     */
    public function getWidgetFactory()
    {
        return $this->getComponent('widgetFactory');
    }

    /**
     * @return \CThemeManager the theme manager.
     */
    public function getThemeManager()
    {
        return $this->getComponent('themeManager');
    }

    /**
     * @return \CTheme the theme used currently. Null if no theme is being used.
     */
    public function getTheme()
    {
        if (is_string($this->_theme)) {
            $this->_theme = $this->getThemeManager()->getTheme($this->_theme);
        }

        return $this->_theme;
    }

    /**
     * @param string $value the theme name
     */
    public function setTheme($value)
    {
        $this->_theme = $value;
    }

    /**
     * @return string the root directory of view files. Defaults to 'protected/views'.
     */
    public function getViewPath()
    {
        if ($this->_viewPath !== null) {
            return $this->_viewPath;
        }

        return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * @param string $path the root directory of view files.
     * @throws \CException if the directory does not exist.
     */
    public function setViewPath($path)
    {
        if (($this->_viewPath = realpath($path)) === false || !is_dir($this->_viewPath)) {
            throw new CException(
                Yii::t('yii', 'The view path "{path}" is not a valid directory.', ['{path}' => $path])
            );
        }
    }

    /**
     * @return string the root directory of system view files. Defaults to 'protected/views/system'.
     */
    public function getSystemViewPath()
    {
        if ($this->_systemViewPath !== null) {
            return $this->_systemViewPath;
        }

        return $this->_systemViewPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'system';
    }

    /**
     * @param string $path the root directory of system view files.
     * @throws CException if the directory does not exist.
     */
    public function setSystemViewPath($path)
    {
        if (($this->_systemViewPath = realpath($path)) === false || !is_dir($this->_systemViewPath)) {
            throw new CException(
                Yii::t('yii', 'The system view path "{path}" is not a valid directory.', ['{path}' => $path])
            );
        }
    }

    /**
     * @return string the root directory of layout files. Defaults to 'protected/views/layouts'.
     */
    public function getLayoutPath()
    {
        if ($this->_layoutPath !== null) {
            return $this->_layoutPath;
        }

        return $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
    }

    /**
     * @param string $path the root directory of layout files.
     * @throws CException if the directory does not exist.
     */
    public function setLayoutPath($path)
    {
        if (($this->_layoutPath = realpath($path)) === false || !is_dir($this->_layoutPath)) {
            throw new CException(
                Yii::t('yii', 'The layout path "{path}" is not a valid directory.', ['{path}' => $path])
            );
        }
    }
}