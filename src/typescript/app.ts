let currentPage = 1;
const logsDataPerPage = 50;

interface Log {
    id: number;
    status_code: string;
    date_submitted: Date;
    webhook_name: string;
    webhook_id: string;
    sended: string;
    issue_id: number,
    bugnote_id: number,
    log_level: string,
    webhook_event: string,
    date_submited: Date
}

const logsData: Log[] = getLogsData()
const logsContainer = document.querySelector("#logs") as HTMLInputElement;
const clearFilter = document.querySelector("#clear_log_filter") as HTMLDivElement;
const filterButton = document.querySelector("#start_filter") as HTMLButtonElement;

// Filters inputs
const issueIdInput = document.querySelector("#issue_id_filter") as HTMLInputElement;
const bugnoteIdInput = document.querySelector("#bugnote_id_filter") as HTMLInputElement;
const logLevelInput = document.querySelector("#log_level_filter") as HTMLInputElement;
const webhookEventInput = document.querySelector("#webhook_event_filter") as HTMLInputElement;
const dateRangePicker = document.querySelector("#date-range-picker") as HTMLInputElement;


displayLogs(logsData, logsDataPerPage);


clearFilter.addEventListener("click", () => {
    displayLogs(logsData, logsDataPerPage);
});


filterButton.addEventListener("click", () => {

    const issueId = issueIdInput.value;
    const bugnoteId = bugnoteIdInput.value;
    const logLevel = logLevelInput.value;
    const webhookEvent = webhookEventInput.value;
    const dateRange = dateRangePicker.value;
    const [startDate, endDate] = getDateRange(dateRange);

    const filteredLogs = logsData.filter((log) => {
        // @ts-ignore
        let date = new Date(log.date_submitted * 1000);
        let options = {year: 'numeric', month: '2-digit', day: '2-digit'};
        // @ts-ignore
        let formattedDate = date.toLocaleDateString('en-US', options).trim();
        console.log(date)
        return (
            (issueId === "" || parseInt(String(log.issue_id)) === parseInt(issueId)) &&
            (bugnoteId === "" || parseInt(String(log.bugnote_id)) === parseInt(bugnoteId)) &&
            log.log_level.toLowerCase().includes(logLevel.toLowerCase()) &&
            log.webhook_event.toLowerCase().includes(webhookEvent.toLowerCase()) &&
            new Date(formattedDate) >= startDate &&
            new Date(formattedDate) <= endDate
        );
    });

    displayLogs(filteredLogs, logsDataPerPage);
});


function getLogsData() {
    const el = document.querySelector('#imaticSynchronizerLogs') as HTMLInputElement;
    if (el == null) {
        return;
    }

    // @ts-ignore
    let data: any = JSON.parse(el.dataset.data);
    return data
}

function getDateRange(dateRange: string): [Date, Date] {
    let dates = dateRange.split(" - ");
    let startDateParts = dates[0].split(".");
    let endDateParts = dates[1].split(".");
    let startDate = new Date(parseInt(startDateParts[2]), parseInt(startDateParts[1]) - 1, parseInt(startDateParts[0]));
    let endDate = new Date(parseInt(endDateParts[2]), parseInt(endDateParts[1]) - 1, parseInt(endDateParts[0]));

    return [startDate, endDate];
}

function parseTimestamp(timestamp: Date) {

    // @ts-ignore
    let date = new Date(timestamp * 1000);
    let day = date.getDate();
    let month = date.getMonth() + 1;
    let year = date.getFullYear();
    let hours = date.getHours();
    let minutes = date.getMinutes()
    let formattedDate = `${day < 10 ? "0" + day : day}.${month < 10 ? "0" + month : month}.${year}`;
    let formatTime = `${hours}:${minutes}`
    return [formattedDate, formatTime]
}

function displayLogs(logsData: Log[], logsDataPerPage: number) {
    logsContainer.innerHTML = "";
    const startIndex = (currentPage - 1) * logsDataPerPage;
    const endIndex = startIndex + logsDataPerPage;
    const currentLogs = logsData.slice(startIndex, endIndex);
    currentLogs.forEach((log) => {
        const logsTd = document.createElement("tr");

        const [parsedDate, parsedTime] = parseTimestamp(log.date_submitted);

        logsTd.innerHTML = `<tr>
            <td><input type="checkbox" name="logs_id" value="${log.id}"></td>
            <td>${log.issue_id}</td>
            <td>${log.bugnote_id}</td>
            <td>${log.log_level}</td>
            <td>${log.webhook_event}</td>
            <td>${log.sended}</td>
            <td>${log.webhook_id}</td>
            <td>${log.webhook_name}</td>
            <td>${parsedDate} ${parsedTime}</td>
            <td>${log.status_code ?? ''}</td>
            </tr>
        `;
        logsContainer.appendChild(logsTd);
    });

    createPagination(logsData, logsDataPerPage);
}

function createPagination(logsData: Log[], logsDataPerPage: number) {
    const totalPages = Math.ceil(logsData.length / logsDataPerPage);
    const paginationContainer = document.querySelector(
        "#logs-pagination"
    ) as HTMLDivElement;
    paginationContainer.innerHTML = "";

    // Create "previous" button
    const prevButton = document.createElement("button");
    prevButton.innerText = "Previous";
    prevButton.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage -= 1;
            displayLogs(logsData, logsDataPerPage);
            updatePaginationButtons();
        }
    });

    // Create logs per page select
    const logsPerPage = document.createElement('select')
    logsPerPage.classList.add("logs_per_page");


    for (let i = 10; i <= 100; i = i + 10) {
        const option = document.createElement('option')

        let selected
        if (selected = i == logsDataPerPage ? true : false)


            option.value = i.toString()
        option.selected = selected
        option.text = i.toString()
        logsPerPage.appendChild(option)
    }
    paginationContainer.prepend(logsPerPage);

    logsPerPage.addEventListener("change", (e) => {
        const selectedOption = e.target as HTMLSelectElement
        const selectedValue = parseInt(selectedOption.value)
        displayLogs(logsData, selectedValue)

    })

    paginationContainer.appendChild(prevButton);

    // Create page buttons
    for (let i = 1; i <= totalPages; i++) {
        const pageButton = document.createElement("button");
        pageButton.innerText = i.toString();
        pageButton.classList.add("page-button");
        pageButton.addEventListener("click", () => {
            currentPage = i;
            displayLogs(logsData, logsDataPerPage);
            updatePaginationButtons();
        });
        paginationContainer.appendChild(pageButton);
    }

    // Create "next" button
    const nextButton = document.createElement("button");
    nextButton.innerText = "Next";
    nextButton.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage += 1;
            displayLogs(logsData, logsDataPerPage);
            updatePaginationButtons();
        }
    });
    paginationContainer.appendChild(nextButton);
    updatePaginationButtons();
}

function updatePaginationButtons() {
    const pageButtons = document.querySelectorAll(".page-button");
    pageButtons.forEach((button) => {
        // @ts-ignore
        if (parseInt(button.innerText) === currentPage) {
            button.classList.add("active-log-page");
        } else {
            button.classList.remove("active-log-page");
        }
    });


    selectMultipleCheckboxex()

    function selectMultipleCheckboxex() {

        // let startCheckbox: HTMLInputElement;
        let endCheckbox: HTMLInputElement;
        let checkboxes: NodeListOf<HTMLInputElement>;

// Načítanie checkboxov do NodeList
        checkboxes = document.querySelectorAll("td input[type='checkbox']");


        let startCheckbox: any = null;
        let lastChecked: any = null;

// Priradenie udalosti click na všetky checkboxy
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener("click", function (event) {
                if (event.shiftKey) {
                    // Ak je stlačený Shift tak označiť/odobrať označenie všetkých checkboxov medzi aktuálne kliknutým checkboxom a startCheckbox
                    let start = Array.from(checkboxes).indexOf(this);
                    let end = Array.from(checkboxes).indexOf(startCheckbox);
                    // Ak start > end, tak výmena hodnôt
                    if (start > end) {
                        [start, end] = [end, start];
                    }
                    for (let j = start; j <= end; j++) {
                        checkboxes[j].checked = lastChecked.checked;
                    }
                } else {
                    // Inak nastaviť aktuálny checkbox ako startCheckbox
                    startCheckbox = this;
                }
                lastChecked = this;
            });
        }
    }
    
}
