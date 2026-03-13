// Feather icons initialization
if (typeof feather !== "undefined") {
    feather.replace();
}

// Get data from window object (passed from blade)
const facultiesWithGroups = window.dashboardData?.facultiesWithGroups || [];
const facultiesWithStudents = window.dashboardData?.facultiesWithStudents || [];
const subjectsWithTopics = window.dashboardData?.subjectsWithTopics || [];
const subjectsWithQuestions = window.dashboardData?.subjectsWithQuestions || [];
const groupsWithStudents = window.dashboardData?.groupsWithStudents || [];

// Get translations
const trans = window.translations || {};

// Chart 1: Faculty Groups Bar Chart
const facultyGroupsOptions = {
    series: [
        {
            name: trans.groups || "Groups",
            data: facultiesWithGroups.map((f) => f.groups_count),
        },
    ],
    chart: {
        type: "bar",
        height: 350,
        toolbar: {
            show: true,
        },
    },
    plotOptions: {
        bar: {
            borderRadius: 8,
            horizontal: false,
            columnWidth: "60%",
            distributed: false,
        },
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: "12px",
            fontWeight: 600,
        },
    },
    colors: ["#7366ff"],
    xaxis: {
        categories: facultiesWithGroups.map((f) => f.name),
        labels: {
            style: {
                fontSize: "12px",
            },
            rotate: -45,
            rotateAlways: false,
        },
    },
    yaxis: {
        title: {
            text: trans.numberOfGroups || "Number of Groups",
        },
    },
    grid: {
        borderColor: "#e7e7e7",
        strokeDashArray: 5,
    },
    tooltip: {
        theme: "light",
        y: {
            formatter: function (val) {
                return val + " " + (trans.groups || "groups");
            },
        },
    },
};

const facultyGroupsChart = new ApexCharts(
    document.querySelector("#facultyGroupsChart"),
    facultyGroupsOptions
);
facultyGroupsChart.render();

// Chart 2: Faculty Students Bar Chart
const facultyStudentsOptions = {
    series: [
        {
            name: trans.students || "Students",
            data: facultiesWithStudents.map((f) => f.students_count),
        },
    ],
    chart: {
        type: "bar",
        height: 350,
        toolbar: {
            show: true,
        },
    },
    plotOptions: {
        bar: {
            borderRadius: 8,
            horizontal: false,
            columnWidth: "60%",
            distributed: false,
        },
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: "12px",
            fontWeight: 600,
        },
    },
    colors: ["#51bb25"],
    xaxis: {
        categories: facultiesWithStudents.map((f) => f.name),
        labels: {
            style: {
                fontSize: "12px",
            },
            rotate: -45,
            rotateAlways: false,
        },
    },
    yaxis: {
        title: {
            text: trans.numberOfStudents || "Number of Students",
        },
    },
    grid: {
        borderColor: "#e7e7e7",
        strokeDashArray: 5,
    },
    tooltip: {
        theme: "light",
        y: {
            formatter: function (val) {
                return val + " " + (trans.students || "students");
            },
        },
    },
};

const facultyStudentsChart = new ApexCharts(
    document.querySelector("#facultyStudentsChart"),
    facultyStudentsOptions
);
facultyStudentsChart.render();

// Chart 3: Group Students Donut Chart
const groupStudentsOptions = {
    series: groupsWithStudents.map((g) => g.students_count),
    chart: {
        type: "donut",
        height: 350,
    },
    labels: groupsWithStudents.map((g) => g.name),
    colors: [
        "#7366ff",
        "#51bb25",
        "#f73164",
        "#a927f9",
        "#f8d62b",
        "#28a745",
        "#17a2b8",
        "#dc3545",
        "#6c757d",
        "#ffc107",
    ],
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val.toFixed(1) + "%";
        },
    },
    legend: {
        position: "bottom",
        fontSize: "12px",
    },
    plotOptions: {
        pie: {
            donut: {
                size: "70%",
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: trans.totalStudents || "Total Students",
                        fontSize: "16px",
                        fontWeight: 600,
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce(
                                (a, b) => a + b,
                                0
                            );
                        },
                    },
                },
            },
        },
    },
    tooltip: {
        theme: "light",
        y: {
            formatter: function (val) {
                return val + " " + (trans.students || "students");
            },
        },
    },
};

const groupStudentsChart = new ApexCharts(
    document.querySelector("#groupStudentsChart"),
    groupStudentsOptions
);
groupStudentsChart.render();

// Chart 4: Subject Questions Column Chart
const subjectQuestionsOptions = {
    series: [
        {
            name: trans.questions || "Questions",
            data: subjectsWithQuestions.map((s) => s.questions_count),
        },
    ],
    chart: {
        type: "bar",
        height: 350,
        toolbar: {
            show: true,
        },
    },
    plotOptions: {
        bar: {
            borderRadius: 8,
            horizontal: true,
            distributed: false,
            barHeight: "70%",
        },
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: "12px",
            fontWeight: 600,
        },
    },
    colors: ["#f8d62b"],
    xaxis: {
        categories: subjectsWithQuestions.map((s) => s.name),
        title: {
            text: trans.numberOfQuestions || "Number of Questions",
        },
    },
    yaxis: {
        labels: {
            style: {
                fontSize: "12px",
            },
        },
    },
    grid: {
        borderColor: "#e7e7e7",
        strokeDashArray: 5,
    },
    tooltip: {
        theme: "light",
        x: {
            show: true,
        },
        y: {
            formatter: function (val) {
                return val + " " + (trans.questions || "questions");
            },
        },
    },
};

const subjectQuestionsChart = new ApexCharts(
    document.querySelector("#subjectQuestionsChart"),
    subjectQuestionsOptions
);
subjectQuestionsChart.render();
