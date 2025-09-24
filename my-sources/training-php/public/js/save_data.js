// save_data.js
async function saveDataToServer(payload) {
  const tokenMeta = document.querySelector('meta[name="csrf-token"]');
  const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

  try {
    const resp = await fetch('/save_data.php', {
      method: 'POST',
      credentials: 'include',               // gửi cookie session nếu cần
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
      },
      body: JSON.stringify(payload)
    });

    if (!resp.ok) {
      const txt = await resp.text();
      throw new Error('Server error: ' + resp.status + ' - ' + txt);
    }
    const data = await resp.json();
    return data;
  } catch (err) {
    console.error('saveDataToServer error:', err);
    throw err;
  }
}

// Example usage: call from your page
document.getElementById('saveBtn')?.addEventListener('click', async function(e){
  e.preventDefault();
  const data = {
    title: document.getElementById('title')?.value || '',
    content: document.getElementById('content')?.value || ''
  };
  try {
    const result = await saveDataToServer(data);
    alert('Saved: ' + result.status);
  } catch (err) {
    alert('Save failed: ' + err.message);
  }
});
