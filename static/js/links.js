document.addEventListener("DOMContentLoaded", function () {
  // Data for official links and section links
  const categorySelect = document.getElementById("category-select");
  const sectionGroupSelect = document.getElementById("section-group-select");
  const linksContainer = document.getElementById("links-container");

  const subjectSelect = document.createElement("select");
  subjectSelect.id = "subject-select";
  subjectSelect.className = "group-select";
  subjectSelect.style.display = "none";

  let subjectOptionsHtml =
    '<option value="" selected disabled>اختر المادة</option>';
  Object.entries(subjectData).forEach(([key, data]) => {
    subjectOptionsHtml += `<option value="${key}">${data.name}</option>`;
  });
  subjectSelect.innerHTML = subjectOptionsHtml;

  document
    .querySelector(".group-links")
    .insertBefore(subjectSelect, sectionGroupSelect);

  categorySelect.addEventListener("change", function () {
    sectionGroupSelect.style.display = "none";
    subjectSelect.style.display = "none";
    linksContainer.innerHTML = "";

    if (this.value === "official") {
      displayOfficialLinks();
    } else if (this.value === "section") {
      sectionGroupSelect.style.display = "block";
    } else if (this.value === "subject") {
      subjectSelect.style.display = "block";
    }
  });

  // Populate section groups based on the selected category
  sectionGroupSelect.addEventListener("change", function () {
    const groupId = this.value;
    fetchLinks({ category: "section", group_code: groupId });
  });

  subjectSelect.addEventListener("change", function () {
    const subject = subjectData[this.value];
    if (subject) {
      displaySubjectLinks(subject);
    }
    fetchLinks({ category: "subject", subject_key: this.value });
  });

  // Initial display of official links
  function displayOfficialLinks() {
    fetchLinks({ category: "official" });
    let html = '<div class="links-grid">';
    officialLinks.forEach((link) => {
      html += `
                <a href="${link.url}" class="link-card" target="_blank" rel="noopener">
                    <i class="${link.icon}"></i>
                    <span>${link.title}</span>
                </a>
            `;
    });
    html += "</div>";
    linksContainer.innerHTML = html;
  }

  // Data for section links
  function displaySectionLinks(groupId, range) {
    let html = '<div class="links-grid">';
    for (let i = range.start; i <= range.end; i++) {
      html += `
                <a href="${sectionLinks[groupId][i]}" class="link-card" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i>
                    <span>سكشن</span>${i}
                </a>
            `;
    }
    html += "</div>";
    linksContainer.innerHTML = html;
  }

  // Data for subject links
  function displaySubjectLinks(subject) {
    let html = `
            <h3>${subject.name}</h3>
            <div class="links-grid">
        `;

    subject.links.forEach((link) => {
      html += `
                <a href="${link.url}" class="link-card" target="_blank" rel="noopener">
                    <i class="${link.icon}"></i>
                    <span>${link.title}</span>
                </a>
            `;
    });

    html += "</div>";
    linksContainer.innerHTML = html;
  }

  // Fetch links based on selected category and parameters
  function fetchLinks(params) {
    const query = new URLSearchParams(params).toString();
    fetch(`getLinks.php?${query}`)
      .then((response) => response.json())
      .then((data) => {
        let html = '<div class="links-grid">';
        data.forEach((link) => {
          html += `
                    <a href="${link.url}" class="link-card" target="_blank" rel="noopener">
                        <i class="${link.icon}"></i>
                        <span>${link.title}</span>
                    </a>
                `;
        });
        html += "</div>";
        linksContainer.innerHTML = html;
      })
      .catch((err) => console.error("Error fetching links:", err));
  }
});
