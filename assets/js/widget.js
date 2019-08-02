/*global Vue */

import Vue from '../../node_modules/vue/dist/vue.js';

Vue.component('twitter-widget', {
    data: function () {
        return {
            tweets: [],
            is_loading: true,
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
              '    <div v-show="is_loading" class="loading">\n' +
              '        <p>Loading...</p>\n' +
              '    </div>\n' +
              '    <div class="tweets">\n' +
              '        <div class="tweet" v-for="tweet in tweets">\n' +
              '            <a target="_blank" :href="tweet.url" v-html="tweet.content"></a>\n' +
              '        </div>\n' +
              '    </div>\n' +
              '</div>',
    methods:  {
        loadTweets: function () {
            let request = new XMLHttpRequest(),
                vueSelf = this;
            request.open('GET', vueSelf.dataUrl, true);
            request.onload = function () {
                let response = JSON.parse(request.responseText);
                if (response.hasOwnProperty('status') && response.status === "OK") {
                    vueSelf.tweets = response.tweets;
                } else {
                    console.log("Error loading");
                    console.log(response);
                }
                vueSelf.is_loading = false;
                vueSelf.callTimeout = setTimeout(vueSelf.loadTweets, vueSelf.dataCallInterval)
            };
            request.onerror = function () {
                vueSelf.is_loading = false;
                vueSelf.callTimeout = setTimeout(vueSelf.loadTweets, 10000);
            };
            vueSelf.is_loading = true;
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