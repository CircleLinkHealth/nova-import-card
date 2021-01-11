<template>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" :href="left.href">
                    <img :src="left.image" :alt="left.alt" style="position:relative;top:-5px" width="50px"/>
                </a>
            </div>

            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li v-if="!item.hidden" v-for="(item, index) in menu" :key="index" role="presentation" class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" :href="item.href" role="button" aria-expanded="false">
                            {{item.text}} <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li v-if="!child.hidden" v-for="(child, index) in item.children" :key="index">
                                <a :href="child.href">{{child.text}}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li v-for="(item, index) in right" :key="index"  class="dropdown">
                        <a :href="item.href">
                            <span v-if="item.info" :class="item.info.className">{{item.info.text}}</span>
                            {{item.text}}
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li v-for="(child, index) in item.children" :key="index">
                                <a :href="child.href" :class="child.className"> {{child.text}}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    import { rootUrl } from '../../app.config';

    export default {
        name: 'NavApp',
        data() {
            return {
                left: {
                    text: '',
                    image: '',
                    alt: '',
                    href: ''
                },
                menu: [
                    {
                        text: '',
                        href: '',
                        hidden: true,
                        icon: {
                            className: 'badge',
                            text: ''
                        },
                        children: [
                            {
                                text: '',
                                href: ''
                            }
                        ]
                    }
                ],
                right: [
                    {
                        text: '',
                        href: '',
                        hidden: true,
                        icon: {
                            className: 'badge',
                            text: ''
                        },
                        children: [
                            {
                                text: '',
                                href: ''
                            }
                        ]
                    }
                ]
            }
        }
    }
</script>

<style>
    
</style>