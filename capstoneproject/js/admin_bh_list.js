const api = {
  list: 'php/get_bh_list.php',
  details: 'php/get_bh_details.php',
  updateAccred: 'php/update_bh_accredit.php',
  updateVerif: 'php/update_owner_verif.php',
  deleteBh: 'php/delete_bh.php'
};

const tableBody = document.querySelector('#bhTable tbody');
const searchInput = document.getElementById('searchInput');
const refreshBtn = document.getElementById('refreshBtn');
const detailsModal = document.getElementById('detailsModal');
const detailsBody = document.getElementById('detailsBody');
const closeModal = document.getElementById('closeModal');
const confirmModal = document.getElementById('confirmModal');
const confirmText = document.getElementById('confirmText');
const confirmYes = document.getElementById('confirmYes');
const confirmNo = document.getElementById('confirmNo');

let bhList = [];
let pendingConfirm = null;

async function fetchList(){
  try{
    const res = await fetch(api.list);
    const data = await res.json();
    if(Array.isArray(data)){
      bhList = data;
      renderTable(bhList);
    } else {
      bhList = [];
      renderTable([]);
      alert('Failed to load list');
    }
  }catch(e){
    console.error(e);
    alert('Error fetching list');
  }
}

function renderTable(list){
  tableBody.innerHTML = '';
  list.forEach(item=>{
    const tr = document.createElement('tr');
    const ownerFull = [item.first_name, item.mid_name, item.last_name].filter(Boolean).join(' ');
    const shortAddress = item.bh_address.length>60? item.bh_address.slice(0,57)+'...': item.bh_address;
    tr.innerHTML = `
      <td>${escapeHtml(item.permit_no)}</td>
      <td>${escapeHtml(item.bh_name)}</td>
      <td>${escapeHtml(shortAddress)}</td>
      <td>${escapeHtml(ownerFull)}</td>
      <td>${escapeHtml(item.cont_no)}</td>
      <td>
        <select class="accred-select" data-permit="${escapeHtml(item.permit_no)}">
          <option value="yes" ${item.accred_status==='yes'?'selected':''}>Accredited</option>
          <option value="pending" ${item.accred_status==='pending'?'selected':''}>Pending</option>
          <option value="no" ${item.accred_status==='no'?'selected':''}>Unaccredited</option>
        </select>
      </td>
      <td>
        <div class="actions">
          <button class="btn view-btn" data-permit="${escapeHtml(item.permit_no)}">View</button>
          <button class="btn verify-btn" data-owner="${escapeHtml(item.owner_id)}" data-verif="${escapeHtml(item.verif_stat)}">${item.verif_stat==='yes'?'Verified':'Verify'}</button>
          <button class="btn delete-btn" data-permit="${escapeHtml(item.permit_no)}">Delete</button>
        </div>
      </td>
    `;
    tableBody.appendChild(tr);
  });
  attachListeners();
}

function attachListeners(){
  document.querySelectorAll('.view-btn').forEach(btn=>{
    btn.onclick = ()=>openDetails(btn.getAttribute('data-permit'));
  });
  document.querySelectorAll('.accred-select').forEach(sel=>{
    sel.onchange = ()=>confirmAction({
      type:'accred',
      permit: sel.getAttribute('data-permit'),
      value: sel.value
    });
  });
  document.querySelectorAll('.verify-btn').forEach(btn=>{
    btn.onclick = ()=>confirmAction({
      type:'verify',
      owner_id: btn.getAttribute('data-owner'),
      current: btn.getAttribute('data-verif')
    });
  });
  document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.onclick = ()=>confirmAction({
      type:'delete',
      permit: btn.getAttribute('data-permit')
    });
  });
}

async function openDetails(permit){
  try{
    const res = await fetch(api.details + '?permit_no=' + encodeURIComponent(permit));
    const data = await res.json();
    if(data && data.permit_no){
      detailsBody.innerHTML = '';
      const b = data;
      const ownerFull = [b.first_name, b.mid_name, b.last_name].filter(Boolean).join(' ');
      const html = `
        <div><strong>Boarding House:</strong> ${escapeHtml(b.bh_name)}</div>
        <div><strong>Address:</strong> ${escapeHtml(b.bh_address)}</div>
        <hr>
        <div><strong>Owner:</strong> ${escapeHtml(ownerFull)}</div>
        <div><strong>Contact:</strong> ${escapeHtml(b.cont_no)}</div>
        <div><strong>Address:</strong> ${escapeHtml(b.owner_address)}</div>
        <div><strong>Verified:</strong> ${escapeHtml(b.verif_stat)}</div>
        <hr>
        <h3>Policies</h3>
        ${renderPolicies(b.policies)}
      `;
      detailsBody.innerHTML = html;
      detailsModal.classList.remove('hidden');
    } else {
      alert('Failed to load details');
    }
  }catch(e){
    console.error(e);
    alert('Error loading details');
  }
}

function renderPolicies(policies){
  if(!Array.isArray(policies) || policies.length===0) return '<div style="padding:8px 0">No policies found.</div>';
  let rows = policies.map(p=>{
    const stat = p.pol_stat==='yes'
      ? `<span style="color:green;font-weight:bold;">YES</span>`
      : `<span style="color:red;font-weight:bold;">NO</span>`;
    return `<tr><td>${escapeHtml(p.pol_name || ('Policy '+p.pol_id))}</td><td>${stat}</td></tr>`;
  }).join('');
  return `
    <table style="width:100%;border-collapse:collapse;margin-top:8px;">
      <thead>
        <tr>
          <th style="text-align:left;border-bottom:1px solid #ccc;">Policy</th>
          <th style="text-align:left;border-bottom:1px solid #ccc;">Status</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function closeDetails(){
  detailsModal.classList.add('hidden');
  detailsBody.innerHTML = '';
}

function confirmAction(payload){
  pendingConfirm = payload;
  let text = '';
  if(payload.type==='accred'){
    text = `Change accreditation of "${payload.permit}" to "${payload.value}"?`;
  } else if(payload.type==='verify'){
    const to = payload.current==='yes'?'no':'yes';
    text = `Set owner verification to "${to}" for owner ID ${payload.owner_id}?`;
  } else if(payload.type==='delete'){
    text = `Delete boarding house with permit "${payload.permit}"? This action cannot be undone.`;
  }
  confirmText.textContent = text;
  confirmModal.classList.remove('hidden');
}

confirmNo.onclick = ()=>{ pendingConfirm = null; confirmModal.classList.add('hidden'); }
closeModal.onclick = ()=>closeDetails();

confirmYes.onclick = async ()=>{
  if(!pendingConfirm){ confirmModal.classList.add('hidden'); return; }
  const p = pendingConfirm;
  confirmModal.classList.add('hidden');
  if(p.type==='accred'){
    try{
      const res = await fetch(api.updateAccred,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({permit_no:p.permit, accred_status:p.value})});
      const j = await res.json();
      if(j.success){ await fetchList(); } else { alert(j.message || 'Failed to update'); }
    }catch(e){ console.error(e); alert('Error updating'); }
  } else if(p.type==='verify'){
    const newVal = p.current==='yes'?'no':'yes';
    try{
      const res = await fetch(api.updateVerif,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({owner_id:p.owner_id, verif_stat:newVal})});
      const j = await res.json();
      if(j.success){ await fetchList(); } else { alert(j.message || 'Failed to update'); }
    }catch(e){ console.error(e); alert('Error updating'); }
  } else if(p.type==='delete'){
    try{
      const res = await fetch(api.deleteBh,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({permit_no:p.permit})});
      const j = await res.json();
      if(j.success){ await fetchList(); } else { alert(j.message || 'Failed to delete'); }
    }catch(e){ console.error(e); alert('Error deleting'); }
  }
  pendingConfirm = null;
};

refreshBtn.onclick = ()=>fetchList();

searchInput.addEventListener('input', ()=>{
  const q = searchInput.value.trim().toLowerCase();
  if(!q){ renderTable(bhList); return; }
  const filtered = bhList.filter(item=>{
    const ownerFull = [item.first_name, item.mid_name, item.last_name].filter(Boolean).join(' ').toLowerCase();
    return (item.permit_no && item.permit_no.toLowerCase().includes(q)) ||
           (item.bh_name && item.bh_name.toLowerCase().includes(q)) ||
           (ownerFull && ownerFull.includes(q)) ||
           (item.cont_no && item.cont_no.toLowerCase().includes(q));
  });
  renderTable(filtered);
});

function escapeHtml(str){
  if(str===null||str===undefined) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

fetchList();
