<?php
use System\Libraries\Render\View;
use System\Libraries\Render\Head;
use System\Libraries\Session;
use App\Libraries\Fastlang;

$me_info = $me_info ?? [];
$allSocials = $me_info['socials'] ?? [];
$socials = [
    'facebook' => $allSocials['facebook'] ?? '',
    'linkedin' => $allSocials['linkedin'] ?? '',
    'telegram' => $allSocials['telegram'] ?? '',
    'whatsapp' => $allSocials['whatsapp'] ?? '',
];

Head::setTitle([Fastlang::__('Profile Settings')]);
view_header();

$fieldStyle = 'width:100%;padding:0.75rem 1rem;border:1px solid #e2e8f0;border-radius:12px;box-sizing:border-box;color:#1e293b;background:#fff;';
$labelStyle = 'display:block;margin-bottom:0.35rem;color:#475569;';
$btnStyle = 'width:100%;padding:0.875rem;background:#2563eb;color:#fff;border:none;border-radius:12px;font-weight:600;cursor:pointer;';
$btnAddStyle = 'display:inline-flex;align-items:center;gap:0.35rem;padding:0.5rem 0.75rem;border:1px solid #93c5fd;border-radius:12px;background:#eff6ff;color:#1d4ed8;font-weight:600;cursor:pointer;font-size:0.875rem;';
$subCardStyle = 'border:1px solid #e2e8f0;border-radius:12px;padding:1rem;margin-bottom:1rem;';
$h3Style = 'margin:0 0 0.75rem;font-size:1rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:0.5rem;';
?>

<main class="v2-auth v2-auth-profile" style="min-height:100vh;padding:2rem;">
  <div class="v2-auth-form-wrap" style="max-width:720px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
      <a href="<?php echo base_url(); ?>" class="v2-auth-back">&larr; <?php _e('Back to Home'); ?></a>
      <h1 style="margin:0;font-size:1.5rem;font-weight:700;color:#1e40af;"><?php _e('Profile Settings'); ?></h1>
      <div><?php echo View::include('language-switcher'); ?></div>
    </div>

    <?php if ($err = Session::flash('error')): ?>
      <div class="v2-auth-msg err"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    <?php if ($success = Session::flash('success')): ?>
      <div class="v2-auth-msg ok"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors) && is_array($errors)): ?>
      <div class="v2-auth-msg err">
        <strong><?php _e('Please fix the following errors:'); ?></strong>
        <ul style="margin:0.5rem 0 0 1rem;padding:0;">
          <?php foreach ($errors as $field => $fieldErrors): ?>
            <?php if (is_array($fieldErrors) && !empty($fieldErrors)): ?>
              <li><?php echo ucfirst(str_replace('_', ' ', $field)); ?>: <?php echo implode(', ', array_map('htmlspecialchars', $fieldErrors)); ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (isset($me_info['role']) && in_array($me_info['role'], ['admin', 'moderator', 'editor', 'author', 'contributor'], true) && function_exists('has_permission') && (has_permission('App\Controllers\Backend\PostsController', 'index') || has_permission('App\Controllers\Backend\HomeController', 'index'))): ?>
      <a href="<?php echo admin_url('home'); ?>" target="_blank" style="display:block;margin-bottom:1.5rem;padding:0.75rem;background:#2563eb;color:#fff;text-align:center;font-weight:600;text-decoration:none;border-radius:12px;"><?php _e('Admin Panel'); ?></a>
    <?php endif; ?>

    <nav style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
      <a href="#personal_info" onclick="v2ProfileTab('personal_info');return false;" id="nav-personal_info" data-tab="personal_info" style="padding:0.5rem 1rem;background:#fff;border:1px solid #e2e8f0;color:#2563eb;text-decoration:none;border-radius:12px;"><?php _e('Personal'); ?></a>
      <a href="#detailed_info" onclick="v2ProfileTab('detailed_info');return false;" id="nav-detailed_info" data-tab="detailed_info" style="padding:0.5rem 1rem;background:#fff;border:1px solid #e2e8f0;color:#64748b;text-decoration:none;border-radius:12px;"><?php _e('Detailed'); ?></a>
      <a href="#social_media" onclick="v2ProfileTab('social_media');return false;" id="nav-social_media" data-tab="social_media" style="padding:0.5rem 1rem;background:#fff;border:1px solid #e2e8f0;color:#64748b;text-decoration:none;border-radius:12px;"><?php _e('Social Media'); ?></a>
      <a href="#security" onclick="v2ProfileTab('security');return false;" id="nav-security" data-tab="security" style="padding:0.5rem 1rem;background:#fff;border:1px solid #e2e8f0;color:#64748b;text-decoration:none;border-radius:12px;"><?php _e('Password'); ?></a>
      <a href="<?php echo auth_url('logout'); ?>" style="padding:0.5rem 1rem;background:#fff;border:1px solid #fecaca;color:#dc2626;text-decoration:none;border-radius:12px;"><?php _e('Logout'); ?></a>
    </nav>

    <section id="personal_info" class="v2-profile-panel v2-auth-card" style="margin-bottom:1rem;">
      <h2 style="margin:0 0 1rem;font-weight:700;color:#1e40af;"><?php _e('Personal Information'); ?></h2>
      <form action="<?php echo auth_url('set-profile'); ?>" method="post">
        <input type="hidden" name="page_type" value="personal_info">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? Session::csrf_token(600); ?>">
        <input type="hidden" name="display" value="0">
        <div style="margin-bottom:1rem;">
          <label style="<?php echo $labelStyle; ?>"><?php _e('Profile Visibility'); ?></label>
          <label style="display:flex;align-items:center;gap:0.5rem;color:#64748b;cursor:pointer;">
            <input type="checkbox" name="display" value="1" <?php echo ($me_info['display'] ?? 0) ? 'checked' : ''; ?>>
            <?php _e('Allow others to find and view your profile'); ?>
          </label>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Full Name'); ?></label><input type="text" name="fullname" value="<?php echo htmlspecialchars($me_info['fullname'] ?? ''); ?>" placeholder="<?php _e('Full Name Placeholder'); ?>" required style="<?php echo $fieldStyle; ?>"></div>
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Username'); ?></label><input type="text" name="username" value="<?php echo htmlspecialchars($me_info['username'] ?? ''); ?>" placeholder="<?php _e('Username Placeholder'); ?>" style="<?php echo $fieldStyle; ?>"></div>
        </div>
        <div style="margin-bottom:1rem;"><label style="<?php echo $labelStyle; ?>"><?php _e('Email Address'); ?></label><input type="email" value="<?php echo htmlspecialchars($me_info['email'] ?? ''); ?>" readonly style="<?php echo $fieldStyle; ?>background:#f1f5f9;color:#64748b;cursor:not-allowed;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Birthday'); ?></label><input type="date" name="birthday" value="<?php echo htmlspecialchars($me_info['birthday'] ?? ''); ?>" style="<?php echo $fieldStyle; ?>"></div>
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Gender'); ?></label><select name="gender" style="<?php echo $fieldStyle; ?>"><option value=""><?php _e('Select Gender'); ?></option><option value="male" <?php echo ($me_info['gender'] ?? '') === 'male' ? 'selected' : ''; ?>><?php _e('Male'); ?></option><option value="female" <?php echo ($me_info['gender'] ?? '') === 'female' ? 'selected' : ''; ?>><?php _e('Female'); ?></option><option value="other" <?php echo ($me_info['gender'] ?? '') === 'other' ? 'selected' : ''; ?>><?php _e('Other'); ?></option></select></div>
        </div>
        <div style="margin-bottom:1rem;"><label style="<?php echo $labelStyle; ?>"><?php _e('Personal Description'); ?></label><textarea name="about_me" rows="4" placeholder="<?php _e('Tell us about yourself...'); ?>" style="<?php echo $fieldStyle; ?>resize:vertical;"><?php echo htmlspecialchars($me_info['about_me'] ?? ''); ?></textarea></div>
        <button type="submit" style="<?php echo $btnStyle; ?>"><?php _e('Update Profile'); ?></button>
      </form>
    </section>

    <section id="detailed_info" class="v2-profile-panel v2-auth-card" style="display:none;margin-bottom:1rem;" x-data="detailedInfo()">
      <h2 style="margin:0 0 0.35rem;font-weight:700;color:#1e40af;"><?php _e('Detailed Information'); ?></h2>
      <p style="margin:0 0 1rem;color:#64748b;font-size:0.875rem;"><?php _e('Manage your professional and personal details'); ?></p>
      <form action="<?php echo auth_url('set-profile'); ?>" method="post" id="detailedInfoForm">
        <input type="hidden" name="page_type" value="detailed_info">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? Session::csrf_token(600); ?>">

        <div style="margin-bottom:1.5rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;">
            <h3 style="<?php echo $h3Style; ?>"><?php _e('Work Experience'); ?></h3>
            <button type="button" @click="addWorkExperience()" style="<?php echo $btnAddStyle; ?>"><?php _e('Add Experience'); ?></button>
          </div>
          <div x-show="workExperiences.length > 0" style="display:flex;flex-direction:column;gap:1rem;">
            <template x-for="(work, index) in workExperiences" :key="index">
              <div style="<?php echo $subCardStyle; ?>">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <span style="font-weight:600;color:#334155;"><?php _e('Experience'); ?> <span x-text="index + 1"></span></span>
                  <button type="button" @click="removeWorkExperience(index)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;">&times;</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Company'); ?></label><input type="text" x-model="work.company" :name="`work_experiences[${index}][company]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Position'); ?></label><input type="text" x-model="work.position" :name="`work_experiences[${index}][position]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Start Date'); ?></label><input type="date" x-model="work.start_date" :name="`work_experiences[${index}][start_date]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('End Date'); ?></label><input type="date" x-model="work.end_date" :name="`work_experiences[${index}][end_date]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div style="grid-column:1/-1;"><label style="<?php echo $labelStyle; ?>"><?php _e('Description'); ?></label><textarea x-model="work.description" :name="`work_experiences[${index}][description]`" rows="3" placeholder="<?php _e('Describe your role and achievements...'); ?>" style="<?php echo $fieldStyle; ?>resize:vertical;"></textarea></div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div style="margin-bottom:1.5rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;">
            <h3 style="<?php echo $h3Style; ?>"><?php _e('Education'); ?></h3>
            <button type="button" @click="addEducation()" style="<?php echo $btnAddStyle; ?>"><?php _e('Add Education'); ?></button>
          </div>
          <div x-show="educations.length > 0" style="display:flex;flex-direction:column;gap:1rem;">
            <template x-for="(edu, index) in educations" :key="index">
              <div style="<?php echo $subCardStyle; ?>">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <span style="font-weight:600;color:#334155;"><?php _e('Education'); ?> <span x-text="index + 1"></span></span>
                  <button type="button" @click="removeEducation(index)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;">&times;</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Institution'); ?></label><input type="text" x-model="edu.institution" :name="`educations[${index}][institution]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Degree'); ?></label><input type="text" x-model="edu.degree" :name="`educations[${index}][degree]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Start Date'); ?></label><input type="date" x-model="edu.start_date" :name="`educations[${index}][start_date]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('End Date'); ?></label><input type="date" x-model="edu.end_date" :name="`educations[${index}][end_date]`" style="<?php echo $fieldStyle; ?>"></div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div style="margin-bottom:1.5rem;">
          <h3 style="<?php echo $h3Style; ?>"><?php _e('Skills'); ?></h3>
          <label style="<?php echo $labelStyle; ?>"><?php _e('Add Skills'); ?></label>
          <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;" x-show="skills.length > 0">
            <template x-for="(skill, index) in skills" :key="index">
              <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.75rem;border-radius:9999px;background:#dbeafe;color:#1e40af;font-size:0.875rem;">
                <span x-text="skill"></span>
                <button type="button" @click="removeSkill(index)" style="background:none;border:none;color:#2563eb;cursor:pointer;padding:0;line-height:1;">&times;</button>
              </span>
            </template>
          </div>
          <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
            <input type="text" x-model="newSkill" @keydown.enter.prevent="addSkill()" placeholder="<?php _e('Enter a skill and press Enter'); ?>" style="<?php echo $fieldStyle; ?>flex:1;">
            <button type="button" @click="addSkill()" style="padding:0.75rem 1rem;background:#2563eb;color:#fff;border:none;border-radius:12px;cursor:pointer;font-weight:600;">+</button>
          </div>
          <p x-show="skills.some(skill => skill.toLowerCase() === newSkill.toLowerCase()) && newSkill.trim() !== ''" style="margin:0;font-size:0.75rem;color:#d97706;"><?php _e('This skill already exists'); ?></p>
          <template x-for="(skill, index) in skills" :key="'h'+index">
            <input type="hidden" :name="`skills[${index}]`" :value="skill">
          </template>
        </div>

        <div style="margin-bottom:1.5rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;">
            <h3 style="<?php echo $h3Style; ?>"><?php _e('Languages'); ?></h3>
            <button type="button" @click="addLanguage()" style="<?php echo $btnAddStyle; ?>"><?php _e('Add Language'); ?></button>
          </div>
          <div x-show="languages.length > 0" style="display:flex;flex-direction:column;gap:1rem;">
            <template x-for="(lang, index) in languages" :key="index">
              <div style="<?php echo $subCardStyle; ?>">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <span style="font-weight:600;color:#334155;"><?php _e('Language'); ?> <span x-text="index + 1"></span></span>
                  <button type="button" @click="removeLanguage(index)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;">&times;</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Language'); ?></label><input type="text" x-model="lang.language" :name="`languages[${index}][language]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Proficiency'); ?></label>
                    <select x-model="lang.proficiency" :name="`languages[${index}][proficiency]`" style="<?php echo $fieldStyle; ?>">
                      <option value=""><?php _e('Select Proficiency'); ?></option>
                      <option value="beginner"><?php _e('Beginner'); ?></option>
                      <option value="intermediate"><?php _e('Intermediate'); ?></option>
                      <option value="advanced"><?php _e('Advanced'); ?></option>
                      <option value="native"><?php _e('Native'); ?></option>
                    </select>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div style="margin-bottom:1.5rem;">
          <h3 style="<?php echo $h3Style; ?>"><?php _e('Hobbies & Interests'); ?></h3>
          <label style="<?php echo $labelStyle; ?>"><?php _e('Add Hobbies'); ?></label>
          <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;" x-show="hobbies.length > 0">
            <template x-for="(hobby, index) in hobbies" :key="index">
              <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.75rem;border-radius:9999px;background:#dcfce7;color:#166534;font-size:0.875rem;">
                <span x-text="hobby"></span>
                <button type="button" @click="removeHobby(index)" style="background:none;border:none;color:#15803d;cursor:pointer;padding:0;line-height:1;">&times;</button>
              </span>
            </template>
          </div>
          <div style="display:flex;gap:0.5rem;margin-bottom:0.25rem;">
            <input type="text" x-model="newHobby" @keydown.enter.prevent="addHobby()" placeholder="<?php _e('Enter a hobby and press Enter'); ?>" style="<?php echo $fieldStyle; ?>flex:1;">
            <button type="button" @click="addHobby()" style="padding:0.75rem 1rem;background:#16a34a;color:#fff;border:none;border-radius:12px;cursor:pointer;font-weight:600;">+</button>
          </div>
          <p x-show="hobbies.some(hobby => hobby.toLowerCase() === newHobby.toLowerCase()) && newHobby.trim() !== ''" style="margin:0;font-size:0.75rem;color:#d97706;"><?php _e('This hobby already exists'); ?></p>
          <template x-for="(hobby, index) in hobbies" :key="'hb'+index">
            <input type="hidden" :name="`hobbies[${index}]`" :value="hobby">
          </template>
        </div>

        <div style="margin-bottom:1.5rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;">
            <h3 style="<?php echo $h3Style; ?>"><?php _e('Certifications & Achievements'); ?></h3>
            <button type="button" @click="addCertification()" style="<?php echo $btnAddStyle; ?>"><?php _e('Add Certification'); ?></button>
          </div>
          <div x-show="certifications.length > 0" style="display:flex;flex-direction:column;gap:1rem;">
            <template x-for="(cert, index) in certifications" :key="index">
              <div style="<?php echo $subCardStyle; ?>">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                  <span style="font-weight:600;color:#334155;"><?php _e('Certification'); ?> <span x-text="index + 1"></span></span>
                  <button type="button" @click="removeCertification(index)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;">&times;</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Name'); ?></label><input type="text" x-model="cert.name" :name="`certifications[${index}][name]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Issuing Organization'); ?></label><input type="text" x-model="cert.issuer" :name="`certifications[${index}][issuer]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Issue Date'); ?></label><input type="date" x-model="cert.issue_date" :name="`certifications[${index}][issue_date]`" style="<?php echo $fieldStyle; ?>"></div>
                  <div><label style="<?php echo $labelStyle; ?>"><?php _e('Expiry Date'); ?></label><input type="date" x-model="cert.expiry_date" :name="`certifications[${index}][expiry_date]`" style="<?php echo $fieldStyle; ?>"></div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <button type="submit" style="<?php echo $btnStyle; ?>"><?php _e('Update Detailed Information'); ?></button>
      </form>
    </section>

    <section id="social_media" class="v2-profile-panel v2-auth-card" style="display:none;margin-bottom:1rem;">
      <h2 style="margin:0 0 1rem;font-weight:700;color:#1e40af;"><?php _e('Social Media'); ?></h2>
      <form action="<?php echo auth_url('set-profile'); ?>" method="post">
        <input type="hidden" name="page_type" value="social_media">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? Session::csrf_token(600); ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Facebook'); ?></label><input type="text" name="facebook" value="<?php echo htmlspecialchars($socials['facebook']); ?>" placeholder="<?php _e('Facebook Username/URL'); ?>" style="<?php echo $fieldStyle; ?>"></div>
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('LinkedIn'); ?></label><input type="text" name="linkedin" value="<?php echo htmlspecialchars($socials['linkedin']); ?>" placeholder="<?php _e('LinkedIn Profile URL'); ?>" style="<?php echo $fieldStyle; ?>"></div>
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('Telegram'); ?></label><input type="text" name="telegram" value="<?php echo htmlspecialchars($socials['telegram']); ?>" placeholder="<?php _e('Telegram Username'); ?>" style="<?php echo $fieldStyle; ?>"></div>
          <div><label style="<?php echo $labelStyle; ?>"><?php _e('WhatsApp'); ?></label><input type="text" name="whatsapp" value="<?php echo htmlspecialchars($socials['whatsapp']); ?>" placeholder="<?php _e('WhatsApp Number'); ?>" style="<?php echo $fieldStyle; ?>"></div>
        </div>
        <button type="submit" style="<?php echo $btnStyle; ?>"><?php _e('Update Social Media'); ?></button>
      </form>
    </section>

    <section id="security" class="v2-profile-panel v2-auth-card" style="display:none;margin-bottom:1rem;">
      <h2 style="margin:0 0 1rem;font-weight:700;color:#1e40af;"><?php _e('Security'); ?></h2>
      <form action="<?php echo auth_url('change-password'); ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? Session::csrf_token(600); ?>">
        <div style="margin-bottom:1rem;"><label style="<?php echo $labelStyle; ?>"><?php _e('Current Password'); ?></label><input type="password" name="current_password" placeholder="<?php _e('Current Password Placeholder'); ?>" required autocomplete="off" style="<?php echo $fieldStyle; ?>"></div>
        <div style="margin-bottom:1rem;"><label style="<?php echo $labelStyle; ?>"><?php _e('New Password'); ?></label><input type="password" name="new_password" placeholder="<?php _e('New Password Placeholder'); ?>" required minlength="6" autocomplete="off" style="<?php echo $fieldStyle; ?>"></div>
        <div style="margin-bottom:1rem;"><label style="<?php echo $labelStyle; ?>"><?php _e('Confirm New Password'); ?></label><input type="password" name="confirm_password" placeholder="<?php _e('Confirm New Password Placeholder'); ?>" required minlength="6" autocomplete="off" style="<?php echo $fieldStyle; ?>"></div>
        <button type="submit" style="<?php echo $btnStyle; ?>"><?php _e('Change Password'); ?></button>
      </form>
    </section>
  </div>
</main>

<script>
<?php
$v2JsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP;
?>
function detailedInfo() {
  return {
    workExperiences: <?php echo json_encode($me_info['work_experiences'] ?? [], $v2JsonFlags); ?>,
    addWorkExperience() {
      this.workExperiences.push({ company: '', position: '', start_date: '', end_date: '', description: '' });
    },
    removeWorkExperience(index) { this.workExperiences.splice(index, 1); },
    educations: <?php echo json_encode($me_info['educations'] ?? [], $v2JsonFlags); ?>,
    addEducation() {
      this.educations.push({ institution: '', degree: '', start_date: '', end_date: '' });
    },
    removeEducation(index) { this.educations.splice(index, 1); },
    skills: <?php echo json_encode($me_info['skills'] ?? [], $v2JsonFlags); ?>,
    newSkill: '',
    addSkill() {
      if (!this.newSkill.trim()) return;
      var skill = this.newSkill.trim();
      if (!this.skills.some(function (s) { return s.toLowerCase() === skill.toLowerCase(); })) this.skills.push(skill);
      this.newSkill = '';
    },
    removeSkill(index) { this.skills.splice(index, 1); },
    languages: <?php echo json_encode($me_info['languages'] ?? [], $v2JsonFlags); ?>,
    addLanguage() { this.languages.push({ language: '', proficiency: '' }); },
    removeLanguage(index) { this.languages.splice(index, 1); },
    hobbies: <?php echo json_encode($me_info['hobbies'] ?? [], $v2JsonFlags); ?>,
    newHobby: '',
    addHobby() {
      if (!this.newHobby.trim()) return;
      var hobby = this.newHobby.trim();
      if (!this.hobbies.some(function (h) { return h.toLowerCase() === hobby.toLowerCase(); })) this.hobbies.push(hobby);
      this.newHobby = '';
    },
    removeHobby(index) { this.hobbies.splice(index, 1); },
    certifications: <?php echo json_encode($me_info['certifications'] ?? [], $v2JsonFlags); ?>,
    addCertification() {
      this.certifications.push({ name: '', issuer: '', issue_date: '', expiry_date: '' });
    },
    removeCertification(index) { this.certifications.splice(index, 1); }
  };
}
function v2ProfileTab(tab) {
  document.querySelectorAll('.v2-profile-panel').forEach(function(el) { el.style.display = 'none'; });
  var p = document.getElementById(tab);
  if (p) p.style.display = 'block';
  document.querySelectorAll('nav [data-tab]').forEach(function(el) {
    el.style.color = el.dataset.tab === tab ? '#2563eb' : '#64748b';
  });
  if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') lucide.createIcons();
}
document.addEventListener('DOMContentLoaded', function() {
  var t = '<?php echo addslashes(Session::flash("activetab") ?? "personal_info"); ?>';
  if (t && document.getElementById(t)) v2ProfileTab(t);
});
</script>

<?php view_footer(); ?>
