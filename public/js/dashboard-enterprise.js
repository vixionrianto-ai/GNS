document.addEventListener("DOMContentLoaded", () => {

    // =====================================================
    // DIGITAL CLOCK
    // =====================================================

    const clock = document.getElementById("clock");
    const serverClock = document.getElementById("serverClock");

    function updateClock() {

        const now = new Date();

        if (clock) {

            clock.textContent = now.toLocaleTimeString("id-ID");

        }

        if (serverClock) {

            serverClock.textContent = now.toLocaleString("id-ID");

        }

    }

    updateClock();

    setInterval(updateClock,1000);

    // =====================================================
    // CHART
    // =====================================================

    if (typeof Chart === "undefined") {

        console.warn("Chart.js belum dimuat.");

        return;

    }

    if (typeof dashboardChartData === "undefined") {

        console.warn("dashboardChartData tidak ditemukan.");

        return;

    }

    const canvas = document.getElementById("incomeChart");

    if (!canvas) return;

    if (window.dashboardIncomeChart) {

        window.dashboardIncomeChart.destroy();

    }

    window.dashboardIncomeChart = new Chart(canvas,{

        type:"line",

        data:dashboardChartData,

        options:{

            responsive:true,

            maintainAspectRatio:false,

            interaction:{

                mode:"index",

                intersect:false

            },

            plugins:{

                legend:{

                    display:false

                },

                tooltip:{

                    backgroundColor:"#1f2937",

                    titleColor:"#fff",

                    bodyColor:"#fff",

                    displayColors:false,

                    padding:12,

                    cornerRadius:8

                }

            },

            elements:{

                line:{

                    tension:.4,

                    borderWidth:3

                },

                point:{

                    radius:4,

                    hoverRadius:7,

                    hitRadius:12

                }

            },

            scales:{

                x:{

                    grid:{

                        display:false

                    }

                },

                y:{

                    beginAtZero:true,

                    grid:{

                        color:"rgba(0,0,0,.05)"

                    },

                    ticks:{

                        precision:0

                    }

                }

            }

        }

    });

});