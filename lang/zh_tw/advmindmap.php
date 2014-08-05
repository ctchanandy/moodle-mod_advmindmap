<?php
$string['advmindmap'] = '進階腦圖';

$string['modulename'] = '進階腦圖';
$string['modulenameplural'] = '進階腦圖';
$string['instances'] = '進階腦圖物件';
$string['pluginadministration'] = '進階腦圖管理';
$string['pluginname'] = '進階腦圖';
$string['detail'] = '進階腦圖';
$string['detail_help'] = '<div class="indent">
<p>腦圖是一種利用圖像式思考輔助工具來表達思維的方法</p>
<table class="advmindmap_help_table">
<tbody>
    <tr>
        <th width="40%">功能</th>
        <th width="60%">方法</th>
    </tr>
    <tr>
        <td>新增節點</td>
        <td>- 滑鼠雙擊任何地方<br />- 點擊綠色的「+」圖示<br />- 按鍵盤&quot;Insert&quot;鍵新增子節點</td>
    </tr>
    <tr>
        <td>刪除節點</td>
        <td>- 按鍵盤&quot;Ctrl&quot;鍵並點擊節點<br />- 點擊紅色的「-」圖示<br />- 按鍵盤&quot;Delete&quot;鍵</td>
    </tr>
    <tr>
        <td>復原/重做</td>
        <td>- 按鍵盤&quot;Ctrl&quot;+&quot;Z&quot;鍵或&quot;Ctrl&quot;+&quot;Y&quot;<br />- 點擊向後和向前的箭咀圖示<br /></td>
    </tr>
    <tr>
        <td>移動節點</td>
        <td>點擊節點並拖移</td>
    </tr>
    <tr>
        <td rowspan="1">儲存腦圖</td>
        <td rowspan="1">點擊最左的「磁碟」圖示</td>
    </tr>
</tbody>
</table>
<p>(此腦圖模組由<a href="http://ekpenso.com">ekpenso.com</a>首先開發。其後<a href="http://www.cite.hku.hk">CITE</a>再作過修改)</p>
</div>';

// capabilities
$string['advmindmap:givecomment'] = '給予評語';
$string['advmindmap:submit'] = '遞交';
$string['advmindmap:view'] = '檢視';
$string['advmindmap:viewother'] = '檢視其他';

$string['advmindmapfieldset'] = 'Custom example fieldset';
$string['advmindmapintro'] = '進階腦圖描述';
$string['advmindmapname'] = '進階腦圖名稱';
$string['editable'] = '可編輯';
$string['lastupdated'] = '最後更新: ';
$string['print'] = '列印: ';
$string['largeconvas'] = '放大編輯區';
$string['smallconvas'] = '縮小編輯區';
$string['notavailable'] = '尚未可用';
$string['viewingauthor'] = '正在檢視 {$a} 的腦圖';
$string['viewown'] = '檢視你自己的腦圖';
$string['class'] = '班別';
$string['classno'] = '班號';
$string['uneditable'] = '腦圖被設定為不可編輯！';
$string['errornotingroup'] = '這是群組腦圖，你既不在任何群組內，也沒有權限檢視其他組的腦圖。';
$string['groupname'] = '群組名稱';
$string['groupmembers'] = '群組成員';
$string['ismember'] = '(&#10004; 你是群組成員)';
$string['viewing'] = '(檢視中)';
$string['editingbyuser'] = '這個群組腦圖正被/曾經被<span style=\'font-weight:bold;\'> {$a} </span>在這個小時內編輯過，因此被鎖定中。';
$string['unlocktime'] = '估計解除鎖定時間尚餘 (如果該用戶沒有再更新)：';
$string['lockedbyyou'] = '這個群組腦圖已被你鎖定來編輯，請點擊這個按鈕來解除鎖定和回到課程：';
$string['unlockbutton'] = '解鎖並回到課程';
$string['unlockconfirm'] = '你確定要解除這個腦圖的鎖定和回到課程嗎？(所有未儲存的改變將會失去)?';
$string['numdummygroups'] = '自動產生群組腦圖';
$string['invalidgroupmodefordummygroups'] = '如果要自動產生群組腦圖，群組模式必需選擇「沒有任何群組」';
$string['uniquelink'] = '本腦圖連結';
$string['copylink'] = '(Ctrl+C 複製)';

$string['invalidid'] = '不正確的advmindmap ID';
$string['errorincorrectcmid'] = '不正確的Course Module ID。';
$string['coursemisconf'] = '課程設定錯誤';
$string['errorinvalidadvmindmap'] = '不正確的 advmindmap instance.';
$string['errorcannotunlockadvmindmap'] = '不能解開腦圖鎖定，不正確的instance.';
$string['errorcannotviewgroupmindmap'] = '你沒有權限檢視其他組的腦圖。';
$string['errorcannotviewusermindmap'] = '你沒有權限檢視其他用戶的腦圖。';
$string['errorinvalidauthor'] = '腦圖作者不是正確的用戶。';
$string['errornostudentincourse'] = '課程內沒有學生。';
$string['errornouseringroup'] = '群組內沒有成員。';
$string['removeinstances'] = '刪除所有腦圖';

// event names
$string['eventmindmapupdated'] = '更新了一個腦圖。';
$string['eventmindmapunlocked'] = '解除了一個腦圖的鎖定';
