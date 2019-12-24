<?php

namespace Mzur\Filesystem;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use OpenStack\OpenStack;
use Illuminate\Support\ServiceProvider;

class SwiftServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['filesystem']->extend('swift', function($app, $config) {
            $options = $this->getOsOptions($config);
            $container = (new OpenStack($options))
                ->objectStoreV1()
                ->getContainer($config['container']);

            $prefix = Arr::get($config, 'prefix', null);
            $url = Arr::get($config, 'url', null);
            $adapter = new SwiftAdapter($container, $prefix, $url);

            return new Filesystem($adapter, $this->getFlyConfig($config));
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function getOsOptions($config)
    {
        if($config['auth'] == 'token') {
            return $this->getTokenOsOptions($config);
        }

        return $this->getAccountOsOptions($config);
    }


    protected function getTokenOsOptions($config)
    {
        $tokenCached = \Auth::user()->authToken();
        return [
            'cachedToken' => $tokenCached,
            'authUrl' => $config['authUrl'],
            'region' => $config['region'],
            'tokenId' => $tokenCached['id'],
        ];
    }
    /**
     * Get the OpenStack options.
     *
     * @param array $config
     *
     * @return array
     */
    protected function getAccountOsOptions($config)
    {
        $options = [
            'authUrl' => $config['authUrl'],
            'region' => $config['region'],
            'user' => [
                'name' => $config['user'],
                'password' => $config['password'],
                'domain' => ['name' => $config['domain']],
            ],
            'debugLog' => Arr::get($config, 'debugLog', false),
            'logger' => Arr::get($config, 'logger', null),
            'messageFormatter' => Arr::get($config, 'messageFormatter', null),
        ];

        if (array_key_exists('projectId', $config)) {
            $options['scope'] = ['project' => ['id' => $config['projectId']]];
        }

        if(Arr::get($config, 'certEnable', false)) {
            $options['requestOptions'] = [
                'verify'  => Arr::get($config, 'certFile', ''),
                'headers' => ['User-Agent' => 'PHP-OPENCLOUD/SDKv1.0'],
            ];
        }

        return $options;
    }

    /**
     * Create the Flysystem configuration.
     *
     * @param array $config
     *
     * @return Config
     */
    protected function getFlyConfig($config)
    {
        $flyConfig = new Config([
            'disable_asserts' => Arr::get($config, 'disableAsserts', false),
        ]);

        $passThroughConfig = [
            'swiftLargeObjectThreshold',
            'swiftSegmentSize',
            'swiftSegmentContainer',
        ];

        foreach ($passThroughConfig as $key) {
            if (isset($config[$key])) {
                $flyConfig->set($key, $config[$key]);
            }
        }

        return $flyConfig;
    }
}
