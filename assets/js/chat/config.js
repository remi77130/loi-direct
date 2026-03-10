window.ChatApp = window.ChatApp || {};

(() => {
  const cfg = window.CHAT_CONFIG || {};

  window.ChatApp.config = {
    BASE: cfg.base || '',
    CSRF: cfg.csrf || '',
    CURRENT_USER_ID: Number(cfg.currentUserId || 0),
  };

  window.ChatApp.dom = {
    RLIST: document.getElementById('rooms'),
    presenceInline: document.getElementById('presenceInline'),
    NST: document.getElementById('roomStatus'),
    chatModal: document.getElementById('chatModal'),
    chatMsgs: document.getElementById('chatMsgs'),
    chatForm: document.getElementById('chatForm'),
    roomIdInp: document.getElementById('room_id'),
    roomTitle: document.getElementById('roomTitle'),
    toBottom: document.getElementById('toBottom'),
    roomDeleteBtn: document.getElementById('roomDeleteBtn'),
    roomShareBtn: document.getElementById('roomShareBtn'),
    chatBackBtn: document.getElementById('chatBackBtn'),
    typingIndicator: document.getElementById('typingIndicator'),

    lockModal: document.getElementById('lockModal'),
    lockForm: document.getElementById('lockForm'),
    lockClose: document.getElementById('lockClose'),
    lockStatus: document.getElementById('lockStatus'),

    showActive: document.getElementById('showActive'),
    activeModal: document.getElementById('activeModal'),
    activeModalBody: document.getElementById('activeModalBody'),
    activeClose: document.getElementById('activeClose'),

    userModal: document.getElementById('userModal'),
    umClose: document.getElementById('umClose'),
    umBody: document.getElementById('umBody'),
    umName: document.getElementById('umName'),

    umBox: document.getElementById('umDMBox'),
    dmForm: document.getElementById('dmSend'),
    dmRecipient: document.getElementById('dmRecipient'),
    dmBody: document.getElementById('dmBody'),
    dmBtn: document.getElementById('dmBtn'),
    dmHint: document.getElementById('dmHint'),
    dmTyping: document.getElementById('dmTyping'),

    imgModal: document.getElementById('imgModal'),
    imgModalImg: document.getElementById('imgModalImg'),

    msgColorInput: document.getElementById('msgColor'),
    privateChk: document.getElementById('is_private'),
    roomPwd: document.getElementById('room_pwd'),
    systemNotice: document.getElementById('systemNotice'),
    systemNoticeClose: document.getElementById('systemNoticeClose'),
  };

  const params = new URLSearchParams(window.location.search);

  window.ChatApp.state = {
    pollTimer: null,
    pollToken: 0,
    pollDelay: 2000,
    lastId: 0,
    currentRoom: 0,
    currentRoomId: 0,
    currentRoomOwner: 0,
    currentRoomName: '',
    lastTypingSent: 0,
    typingInterval: null,
    typingWatchTimer: null,
    dmTarget: 0,
    dmTypingTimer: null,
    lastDmTypingSent: 0,
    initialRoomId: parseInt(params.get('room') || '0', 10) || 0,
    initialMessageId: parseInt(params.get('msg') || '0', 10) || 0,
    initialMessageFocused: false,
  };
})();