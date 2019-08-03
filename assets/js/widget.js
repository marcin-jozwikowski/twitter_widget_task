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
            callTimeout: null
        }
    },
    computed: {
        dataUrl: function () {
            let dataPath = this.url + this.data_path;
            if (this.tweets.length > 0) {
                dataPath += "/" + this.tweets[0]["id"];
            }
            return dataPath;
        },
        styleUrl: function () {
            return this.url + this.style_path;
        }
    },
    props: {
        'url': {
            required: true
        },
        'data_path': {
            required: true
        },
        'style_path': {
            default: "/build/twitterWidgetStyle.css"
        },
        'interval': {
            default: 60000
        }
    },
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
        '            <a target="_blank" :href="tweet.url">' +
        '               <p class="created"  v-html="tweet.created"></p>' +
        '               <p class="content" v-html="tweet.content"></p>' +
        '            </a>\n' +
        '            <a target="_blank" class="link" v-for="link in tweet.links" :href="link.url">See attachment</a>\n' +
        '        </div>\n' +
        '    </div>\n' +
        '</div>',
    methods: {
        setTweets: function (newTweets) {
            for (let id in newTweets) {
                if (newTweets.hasOwnProperty(id)) {
                    this.tweets.unshift(newTweets[id]);
                }
            }
            this.status = this.statuses.ok;
        },
        setError: function (error) {
            this.errorMessage = error;
            this.status = this.statuses.error;
        },
        onNetworkError: function () {
            this.setError("Network Connection Error");
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
                vueSelf.callTimeout = setTimeout(vueSelf.loadTweets, vueSelf.interval)
            };
            request.onerror = vueSelf.onNetworkError;
            request.ontimeout = vueSelf.onNetworkError;
            vueSelf.status = vueSelf.statuses.loading;
            request.send();
        }
    }
});

export function init(elemID, baseUrl, feedPath) {
    const runApp = function () {
        document.getElementById(elemID).innerHTML = "<twitter-widget :url='baseUrl' :data_path='feedPath'></twitter-widget>";
        new Vue({
            el: '#' + elemID,
            data: {
                baseUrl: baseUrl,
                feedPath: feedPath
            }
        });
    };
    if (window.addEventListener) window.addEventListener('load', runApp, false);
    else if (window.attachEvent) window.attachEvent('onload', runApp);
    else runApp();
}