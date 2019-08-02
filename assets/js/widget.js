/*global Vue */

import Vue from '../../node_modules/vue/dist/vue.js';

Vue.component('twitter-widget', {
    data: function () {
        return {
            tweets: [],
            statuses: {
                ok: 0,
                loading: 1,
                error: 2
            },
            status: 1,
            errorMessage: null,
            dataCallInterval: this.interval,
            callTimeout: null
        }
    },
    computed: {
        dataUrl: function () {
            return this.url + this.path;
        },
        styleUrl: function () {
            return this.url + "/build/twitterWidgetStyle.css";
        }
    },
    props: [
        'url',
        'path',
        'interval'
    ],
    mounted: function () {
        this.loadTweets();
    },
    template: '<div id="twitter_widget">\n' +
        '    <link rel="stylesheet" type="text/css" :href="styleUrl">\n' +
        '    <div v-show="this.status==this.statuses.loading" class="loading">\n' +
        '        <p>Loading...</p>\n' +
        '    </div>\n' +
        '    <div v-show="this.status==this.statuses.error" class="error">\n' +
        '        <p v-html="errorMessage"></p>\n' +
        '    </div>\n' +
        '    <div class="tweets">\n' +
        '        <div class="tweet" v-for="tweet in tweets">\n' +
        '            <a target="_blank" :href="tweet.url" v-html="tweet.content"></a>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>',
    methods: {
        setTweets: function (newTweets) {
            this.tweets = newTweets;
            this.status = this.statuses.ok;
        },
        setError: function (error) {
            this.errorMessage = error;
            this.status = this.statuses.error;
        },
        onNetworkError: function() {
            this.setError("Network Connection Error")
            this.callTimeout = setTimeout(this.loadTweets, 10000);
        },
        loadTweets: function () {
            let request = new XMLHttpRequest(),
                vueSelf = this;
            request.open('GET', vueSelf.dataUrl, true);
            request.onload = function () {
                let response;
                try {
                    response = JSON.parse(request.responseText);
                } catch (e) {
                    vueSelf.setError("Data corrupted");
                    return;
                }
                if (response.hasOwnProperty('status') && response.status === "OK") {
                    vueSelf.setTweets(response.tweets)
                } else {
                    vueSelf.setError(response.message);
                }
                vueSelf.callTimeout = setTimeout(vueSelf.loadTweets, vueSelf.dataCallInterval)
            };
            request.onerror = vueSelf.onNetworkError;
            request.ontimeout = vueSelf.onNetworkError;
            vueSelf.status = vueSelf.statuses.loading;
            request.send();
        }
    }
});

let testFeed = {
    defaultParameters: {
        loadInterval: 60000
    },

    init: function (elemID, params) {
        const runApp = function () {
            document.getElementById(elemID).innerHTML = "<twitter-widget :url='params.url' :path='params.feedPath' :interval='params.loadInterval'></twitter-widget>";
            new Vue({
                el: '#' + elemID,
                data: {
                    params: Object.assign(testFeed.defaultParameters, params)
                }
            });
        };
        if (window.addEventListener) window.addEventListener('load', runApp, false);
        else if (window.attachEvent) window.attachEvent('onload', runApp);
        else runApp();
    }
};

export function init(elemID, baseUrl, feed) {
    testFeed.init(elemID, {
        url: baseUrl,
        feedPath: feed
    });
}