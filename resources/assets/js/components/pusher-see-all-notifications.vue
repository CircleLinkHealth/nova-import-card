<template>
    <div class="container">
        <div v-if="totalNotifications !== 0">
            <full-screen-loader v-show="loader" class="half-screen-loader"></full-screen-loader>
            <div class="title"><h2>YOUR NOTIFICATIONS</h2></div>
            <ul class="list-group list-group-flush notifications">
                <li v-for="notification in notifications.notifications" :key="notification.id">
                    <a class="list-group-item list-group-item-action"
                       :class="{greyOut: notification.read_at !== undefined && notification.read_at !== null}"
                       @click="redirectAndMarkAsRead(notification)"
                       v-html="showAll(notification)">
                    </a>
                </li>
            </ul>

            <nav class="pagination">
                <ul class="pagination">
                    <button type="button"
                            data-cy="paginatorPrevPage"
                            class="btn btn-primary"
                            :disabled="disablePrevButton"
                            :class="{disable: disablePrevButton}"
                            @click="prevPage">
                        Prev
                    </button>

                    <li v-for="(page, index) in paginationTriggers">
                        <button v-if="hidePagesIfOneLeft()"
                                style="padding: 10px; margin:5px;"
                                type="button"
                                data-cy="pages"
                                class="btn btn-light"
                                :class="{'pages': roundUp(page) === currentPage}"
                                @click="goToPage(page)">
                            {{roundUp(page)}}
                        </button>
                    </li>


                    <button type="button"
                            data-cy="paginatorNextPage"
                            class="btn btn-primary"
                            :disabled="disableNextButton"
                            :class="{disable: disableNextButton}"
                            @click="nextPage">
                        Next
                    </button>
                    <vue-select class="page-dropdown"
                                :clearable="false"
                                :options="dropdownOptions"
                                @input="submitDropdownValue"
                                v-model="selectedResultsPerPage">
                    </vue-select>

                </ul>
            </nav>
        </div>
        <div v-else>
            <div class="title"><h3>You dont have any notifications at this moment</h3></div>
        </div>
    </div>
</template>

<script>

    const visiblePagesLimit = 10;
    const defaultDropdown = {
        label: 'Show 5 /page',
        value: 5
    };
    import VueSelect from 'vue-select';
    import FullScreenLoader from ".../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/NursesWorkSchedules/FullScreenLoader";

    export default {
        name: "pusher-see-all-notifications",
        components: {
            'vue-select': VueSelect,
            FullScreenLoader,
        },
        props: [],

        data() {
            return {
                notifications: {},
                currentPage: 1,
                totalPages: '',
                totalNotifications: '',
                visiblePagesCount: '',
                loader: false,
                selectedResultsPerPage: defaultDropdown,
                dropdownOptions: [
                    defaultDropdown,
                    {
                        label: 'Show 10 /page',
                        value: 10
                    },
                    {
                        label: 'Show 15 /page',
                        value: 15
                    },
                    {
                        label: 'Show 20 /page',
                        value: 20
                    },
                    {
                        label: 'Show 30 /page',
                        value: 30
                    },
                    {
                        label: 'Show 40 /page',
                        value: 40
                    },
                ],
            }
        },

        computed: {
            paginationTriggers() {
                if (this.visiblePagesCount !== '' && this.visiblePagesCount !== 0) {
                    const currentPage = this.currentPage;
                    const totalPages = this.totalPages;
                    const visiblePagesCount = this.visiblePagesCount;
                    const visiblePagesThreshold = (visiblePagesCount - 1) / 2;
                    const pagintationTriggersArray = Array(visiblePagesCount - 1).fill(0);

                    //Scenario 1: The selected page number is smaller than half of the list width
                    if (currentPage <= visiblePagesThreshold + 1) {
                        pagintationTriggersArray[0] = 1;
                        const pagintationTriggers = pagintationTriggersArray.map(
                            (paginationTrigger, index) => {
                                return pagintationTriggersArray[0] + index
                            }
                        );
                        pagintationTriggers.push(totalPages);
                        return pagintationTriggers
                    }

                    //Scenario 2: The selected page number is bigger than half of the list width counting from the end of the list
                    if (currentPage >= totalPages - visiblePagesThreshold + 1) {
                        const pagintationTriggers = pagintationTriggersArray.map(
                            (paginationTrigger, index) => {
                                return totalPages - index
                            }
                        );
                        pagintationTriggers.reverse().unshift(1);
                        return pagintationTriggers
                    }

                    // Scenario 3: All other cases
                    pagintationTriggersArray[0] = currentPage - visiblePagesThreshold + 1;
                    const pagintationTriggers = pagintationTriggersArray.map(
                        (paginationTrigger, index) => {
                            return pagintationTriggersArray[0] + index;
                        }
                    );
                    pagintationTriggers.unshift(1);
                    pagintationTriggers[pagintationTriggers.length - 1] = totalPages;
                    return pagintationTriggers
                }
                // If no notifications then just return 1 page.
                return 1;
            },

            disableNextButton() {
                return this.currentPage === this.totalPages;
            },

            disablePrevButton() {
                return this.currentPage === 1;
            }
        },

        methods: {
            hidePagesIfOneLeft() {
                return this.currentPage !== 1 && this.totalPages !== 1;
            },
            submitDropdownValue(value) {
                const page = 1;
                this.currentPage = page;
                this.getResults(page);
            },

            roundUp(page) {
                return Math.ceil(page);
            },

            setCurrentPage(page) {
                this.currentPage = page;
                if (page < 1) {
                    this.currentPage = 1;
                } else if (page > this.totalPages) {
                    this.currentPage = this.totalPages;
                } else {
                    this.currentPage = page;
                }
            },

            goToPage(page) {
                const val = this.roundUp(page);
                this.setCurrentPage(val);
                this.getResults(val);
            },

            nextPage() {
                const nextPage = this.currentPage + 1;
                this.setCurrentPage(nextPage);
                this.getResults(nextPage);
            },

            prevPage() {
                const prevPage = this.currentPage - 1;
                this.setCurrentPage(prevPage);
                this.getResults(prevPage);
            },

            calculateVisiblePages() {
                return this.totalPages > visiblePagesLimit ? visiblePagesLimit : this.totalPages;
            },

            getResults(page) {
                this.loader = true;
                const resultsPerPage = this.selectedResultsPerPage.value;
                axios.get(`all-notifications-pages/${page}/${resultsPerPage}`).then(response => {
                    this.notifications = response.data;
                    this.totalPages = response.data.totalPages;
                    this.totalNotifications = response.data.totalNotifications;
                    this.visiblePagesCount = this.calculateVisiblePages();
                    this.loader = false;
                }).catch((error) => {
                    console.log(error);
                });
            },


            showAll(notification) { //this is the same function as in pusher-notifications.vue, it should be  extracted
                const getNotificationSubject = notification.data.subject;
                const getNotificationElapsedTime = notification.elapsed_time;
                return `${getNotificationSubject}
                        <br><div style="padding-top: 1%">${getNotificationElapsedTime}</div>`;

            },

            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-mark-read/${notification.id}`)
                    .then(response => {
                            this.redirectTo(notification);
                        }
                    );
            },

            redirectTo(notification) {
                window.location.href = notification.data.redirect_link;
            },
        },

        mounted() {
            const page = 1;
            this.getResults(page);
        },

        created() {

        }

    }
</script>

<style scoped>

    .notifications {
        overflow-y: scroll;
        height: 85%;
    }

    .title {
        color: black;
        font-weight: bold;
        margin-bottom: 2%;
        margin-top: 8%;
        margin-left: 1%;
    }

    a.list-group-item {
        border-left: unset;
        border-right: unset;
        padding: 1.5%;
        font-size: initial;
    }

    a.list-group-item:hover {
        background: #ecf8ff;
        cursor: pointer;
    }

    .greyOut {
        opacity: 0.6;
    }

    .pages {
        font-size: 25px;
        font-weight: bolder;
        color: #4eb1e2
    }

    .btn {
        height: 50px;
        outline: none;
        padding: 10px;
    }

    .disable {
        background-color: #f4f6f6;
        color: #d5dbdb;
        cursor: default;
        opacity: 0.7;
    }

    .pagination {
        display: inline-flex;
    }

    .page-dropdown {
        padding-left: 30px;
        padding-top: 10px
    }

    #cover-spin {
        background-color: unset;
    }

    #cover-spin::after {
        border-right-color: #47bdab;
        border-bottom-color: #47bdab;
        border-left-color: #47bdab;
        border-top-color: transparent;
    }
</style>