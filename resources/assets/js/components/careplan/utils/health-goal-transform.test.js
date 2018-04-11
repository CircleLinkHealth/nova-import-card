import transformHealthGoal from './health-goal-transform'

const BLOOD_PRESSURE_TARGET = '130/80'
const BLOOD_SUGAR_TARGET = '120'


describe('Health Goal Transform Verbs', () => {
    describe('Blood Pressure', () => {
        describe('Starting is NULL', () => {
            it('Should be Regulate', () => {
                const goal = {
                    name: 'Blood Pressure',
                    info: {
                        starting: null,
                        target: BLOOD_PRESSURE_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Regulate')
            })
        })
        
        describe('Starting < 130/xx', () => {
            it('Should be Regulate', () => {
                const goal = {
                    name: 'Blood Pressure',
                    info: {
                        starting: '129',
                        target: BLOOD_PRESSURE_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Regulate')
            })
        })

        describe('Starting > 130/xx', () => {
            it('Should be Decrease', () => {
                const goal = {
                    name: 'Blood Pressure',
                    info: {
                        starting: '131',
                        target: '220'
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Decrease')
            })
        })
    })

    describe('Blood Sugar', () => {
        describe('Starting is NULL', () => {
            it('Should be Regulate', () => {
                const goal = {
                    name: 'Blood Sugar',
                    info: {
                        starting: null,
                        target: BLOOD_SUGAR_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Regulate')
            })
        })

        describe('Starting > 130', () => {
            it('Should be Decrease', () => {
                const goal = {
                    name: 'Blood Sugar',
                    info: {
                        starting: '131',
                        target: BLOOD_SUGAR_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Decrease')
            })
        })

        describe('Starting > 80 && Starting <= 130', () => {
            it('Should be Regulate', () => {
                const goal = {
                    name: 'Blood Sugar',
                    info: {
                        starting: '129',
                        target: BLOOD_SUGAR_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Regulate')
            })
        })

        describe('Starting <= 80', () => {
            it('Should be Increase', () => {
                const goal = {
                    name: 'Blood Sugar',
                    info: {
                        starting: '79',
                        target: BLOOD_SUGAR_TARGET
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Increase')
            })
        })
    })

    describe('Weight', () => {
        describe('target < starting', () => {
            it('Should be Decrease', () => {
                const goal = {
                    name: 'Weight',
                    info: {
                        starting: '7',
                        target: '5'
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Decrease')
            })
        })

        describe('target > starting', () => {
            it('Should be Increase', () => {
                const goal = {
                    name: 'Weight',
                    info: {
                        starting: '5',
                        target: '7'
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Increase')
            })
        })
        
        describe('target is null', () => {
            it('Should be Regulate', () => {
                const goal = {
                    name: 'Weight',
                    info: {
                        starting: '200',
                        target: null
                    }
                }

                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.verb).toEqual('Regulate')
            })
        })
    })
})