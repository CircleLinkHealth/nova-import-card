import transformHealthGoal from '../health-goal-transform'

const BLOOD_PRESSURE_TARGET = '130/80'
const BLOOD_SUGAR_TARGET = '120'


describe('Health Goal Transform Verbs', () => {
    describe('No Info', () => {
        describe('type 0', () => {
            const goal = {
                type: 0
            }
            const transformedGoal = transformHealthGoal(goal)

            it('should have an info prop', () => {
                expect(typeof(transformedGoal.info)).toEqual('object')
            })

            it('should have a monitor_changes_for_chf prop', () => {
                expect(transformedGoal.info.monitor_changes_for_chf).toEqual(0)
            })
        })
        describe('type 1', () => {
            const goal = {
                type: 1
            }
            const transformedGoal = transformHealthGoal(goal)

            it('has an info prop', () => {
                expect(typeof(transformedGoal.info)).toEqual('object')
            })

            it('has necessary info props', () => {
                expect(transformedGoal.info.systolic_high_alert).toEqual(180)
                expect(transformedGoal.info.systolic_low_alert).toEqual(80)
                expect(transformedGoal.info.diastolic_high_alert).toEqual(90)
                expect(transformedGoal.info.diastolic_low_alert).toEqual(40)
                expect(transformedGoal.info.target).toEqual('130/80')
            })
        })
        describe('type 2', () => {
            const goal = {
                type: 2
            }
            const transformedGoal = transformHealthGoal(goal)

            it('has an info prop', () => {
                expect(typeof(transformedGoal.info)).toEqual('object')
            })

            it('has necessary info props', () => {
                expect(transformedGoal.info.high_alert).toEqual(350)
                expect(transformedGoal.info.low_alert).toEqual(60)
                expect(transformedGoal.info.starting_a1c).toEqual(0)
                expect(transformedGoal.info.target).toEqual('120')
            })
        })
    })

    describe('Prop Changes', () => {
        it('should reset [info.starting] if info.starting === 0', () => {
            const goal = {
                name: 'Blood Pressure',
                info: {
                    starting: '0'
                }
            }

            const transformedGoal = transformHealthGoal(goal)

            expect(transformedGoal.info.starting).toEqual('')
        })

        it('should throw if [name] is not defined', () => {
            const goal = {
                info: {
                    starting: '0'
                }
            }

            expect(() => {
                const transformedGoal = transformHealthGoal(goal)
            }).toThrow()
        })

        describe('Bloog Sugar [info.target] === "0"', () => {
            it('should set [info.target] to "120"', () => {
                const goal = {
                    name: 'Blood Sugar',
                    info: {
                        starting: '0',
                        target: '0'
                    }
                }
    
                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.target).toEqual('120')
            })
        })

        describe('Bloog Pressure [info.target] === "0"', () => {
            it('should set [info.target] to "130/80"', () => {
                const goal = {
                    name: 'Blood Pressure',
                    info: {
                        starting: '0',
                        target: '0'
                    }
                }
    
                const transformedGoal = transformHealthGoal(goal)

                expect(transformedGoal.info.target).toEqual('130/80')
            })
        })
    })

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