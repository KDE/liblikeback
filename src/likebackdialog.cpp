/*
    Copyright © 2006 Sebastien Laout
    Copyright © 2008-2009 Valerio Pilo <valerio@kmess.org>
    Copyright © 2008-2009 Sjors Gielen <dazjorz@kmess.org>
    Copyright © 2010 Teo Mrnjavac <teo@kde.org>
    Copyright © 2010 Thiago Macieira <thiago@kde.org>
    Copyright © 2010 Harald Sitter <sitter@kde.org>

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License as
    published by the Free Software Foundation; either version 2 of
    the License or (at your option) version 3 or any later version
    accepted by the membership of KDE e.V. (or its successor approved
    by the membership of KDE e.V.), which shall act as a proxy
    defined in Section 14 of version 3 of the license.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

#include "likebackdialog.h"

#include <KAboutData>
#include <KApplication>
#include <KConfig>
#include <KDebug>
#include <KMessageBox>
#include <KPushButton>
#include <KIO/Job>

#include "likeback.h"

extern int likeBackDebugArea();

LikeBackDialog::LikeBackDialog(LikeBack::ButtonCodes reason, const QString &initialComment,
                               const QString &windowPath, const QString &context, LikeBack *likeBack)
        : KDialog(kapp->activeWindow())
        , Ui::LikeBackDialog()
        , m_context(context)
        , m_likeBack(likeBack)
        , m_windowPath(windowPath)
{
    setCaption(i18n("Send a Comment to the Developers"));
    setButtons(Ok | Cancel);
    setDefaultButton(Ok);
    setObjectName("LikeBackFeedBack");
    showButtonSeparator(true);
    restoreDialogSize(KGlobal::config()->group("LikeBackDialog"));

    // Set up the user interface
    QWidget *mainWidget = new QWidget(this);
    setupUi(mainWidget);
    setMainWidget(mainWidget);
    mainWidget->setMinimumSize(400, 400);
    mainWidget->setSizePolicy(QSizePolicy::MinimumExpanding, QSizePolicy::MinimumExpanding);

    m_typeGroup_ = new QButtonGroup(this);
    m_typeGroup_->addButton(likeRadio_,    LikeBack::Like);
    m_typeGroup_->addButton(dislikeRadio_, LikeBack::Dislike);
    m_typeGroup_->addButton(bugRadio_,     LikeBack::Bug);
    m_typeGroup_->addButton(featureRadio_, LikeBack::Feature);

    LikeBack::ButtonCodes buttons = m_likeBack->buttons();
    likeRadio_   ->setVisible(buttons & LikeBack::Like);
    dislikeRadio_->setVisible(buttons & LikeBack::Dislike);
    bugRadio_    ->setVisible(buttons & LikeBack::Bug);
    featureRadio_->setVisible(buttons & LikeBack::Feature);

    if (reason == LikeBack::AllButtons || reason == LikeBack::DefaultButtons) {
        if (buttons & LikeBack::Dislike) reason = LikeBack::Dislike;
        else if (buttons & LikeBack::Bug) reason = LikeBack::Bug;
        else if (buttons & LikeBack::Feature) reason = LikeBack::Feature;
        else                                   reason = LikeBack::Like;
    }

    switch (reason) {
    case LikeBack::Like:    likeRadio_   ->setChecked(true); break;
    case LikeBack::Dislike: dislikeRadio_->setChecked(true); break;
    case LikeBack::Bug:     bugRadio_    ->setChecked(true); break;
    case LikeBack::Feature: featureRadio_->setChecked(true); break;
    default: break; // Will never arrive here
    }

    connect(m_comment, SIGNAL(textChanged()),
            this,      SLOT(verify()));

    if (m_windowPath.isEmpty()) {
        m_windowPath = LikeBack::activeWindowPath();
    }

    m_comment->setPlainText(initialComment);
    m_comment->setFocus();

    emailAddressEdit_->setText(m_likeBack->emailAddress());
    specifyEmailCheckBox_->setChecked(true);

    m_informationLabel->setText(introductionText());
    setMinimumSize(minimumSizeHint());

    verify();
}

LikeBackDialog::~LikeBackDialog()
{
    KConfigGroup group = KGlobal::config()->group("LikeBackDialog");
    saveDialogSize(group);
}

QString LikeBackDialog::introductionText()
{
    QStringList acceptedLocales;
    KLocale *kLocale = KGlobal::locale();
    QStringList acceptedLocaleCodes = m_likeBack->acceptedLocales();

    if (! acceptedLocaleCodes.isEmpty()) {
        foreach(const QString &locale, acceptedLocaleCodes) {
            acceptedLocales << kLocale->languageCodeToName(locale);
        }
    } else if (! kLocale->language().startsWith(QLatin1String("en"))) {
        acceptedLocales << kLocale->languageCodeToName("en");
    }

    QString languagesMessage;
    if (! acceptedLocales.isEmpty()) {
        // TODO: Replace the URL with a localized one:
        QString translationTool("http://www.google.com/language_tools?hl=" + kLocale->language());

        if (acceptedLocales.count() == 1) {
            languagesMessage = i18nc("Feedback dialog text, message with one accepted language for the comments",
                                     "Please, write it in <b>%1</b> (you may want to use an <a href=\"%2\">online translation tool</a> for this).<br/>",
                                     acceptedLocales.first(),
                                     translationTool);
        } else {
            languagesMessage = i18nc("Feedback dialog text, message with list of accepted languages for the comments",
                                     "Please, write it in <b>%1 or %2</b> (you may want to use an <a href=\"%3\">online translation tool</a> for this).<br/>",
                                     QStringList(acceptedLocales.mid(0, -2)).join(", "),
                                     acceptedLocales.last(),
                                     translationTool);
        }
    }

    QString balancingMessage;
    if (m_likeBack->isLikeActive() && m_likeBack->isDislikeActive()
            && (m_typeGroup_->checkedId() == LikeBack::Like || m_typeGroup_->checkedId() == LikeBack::Dislike)) {
        balancingMessage = i18nc("Feedback dialog text, message to remind to balance the likes and dislikes",
                                 "To make the comments you send more useful in improving this application, "
                                 "try to send the same amount of positive and negative comments.<br/>");
    }

    QString noFeatureRequestsMessage;
    if (! m_likeBack->isFeatureActive()) {
        noFeatureRequestsMessage = i18nc("Feedback dialog text, text to disallow feature requests",
                                         "Please, do not ask for new features: this kind of request will be ignored.<br/>");
    }

    return i18nc("Feedback dialog text, %1=Application name,%2=message with list of accepted languages for the comment,"
                 "%3=optional text to remind to balance the likes and dislikes,%4=optional text to disallow feature requests.",
                 "<p>You can provide the developers a brief description of your opinions about %1.<br/>"
                 "%2 " // %2: Contains the newline if present
                 "%3%4</p>",
                 m_likeBack->aboutData()->programName(),
                 languagesMessage,
                 balancingMessage,
                 noFeatureRequestsMessage);
}

void LikeBackDialog::verify()
{
    bool hasComment = (! m_comment->document()->isEmpty());
    bool hasType    = (m_typeGroup_->checkedId() != -1);

    button(Ok)->setEnabled(hasComment && hasType);
}

void LikeBackDialog::slotButtonClicked(int buttonId)
{
    if (buttonId != Ok) {
        KDialog::slotButtonClicked(buttonId);
        return;
    }

    QString type;
    QString emailAddress;

    if (specifyEmailCheckBox_->isChecked()) {
        emailAddress = emailAddressEdit_->text();

        // lame-ass way to check if the e-mail address is valid:
        if (!emailAddress.contains(QRegExp("^[A-Z0-9._%\\-]+@(?:[A-Z0-9\\-]+\\.)+[A-Z]{2,4}$", Qt::CaseInsensitive))) {
            KMessageBox::error(this, i18n("The email address you have entered is not valid, and cannot be used: '%1'", emailAddress));
            return;
        }

        m_likeBack->setEmailAddress(emailAddress, true);
    }

    m_comment->setEnabled(false);
    button(Ok)->setEnabled(false);

    switch (m_typeGroup_->checkedId()) {
    case LikeBack::Like:    type = "Like";    break;
    case LikeBack::Dislike: type = "Dislike"; break;
    case LikeBack::Bug:     type = "Bug";     break;
    case LikeBack::Feature: type = "Feature"; break;
    }

    QString data("protocol=" + QUrl::toPercentEncoding("1.0")                              + '&' +
                 "type="     + QUrl::toPercentEncoding(type)                               + '&' +
                 "version="  + QUrl::toPercentEncoding(m_likeBack->aboutData()->version()) + '&' +
                 "locale="   + QUrl::toPercentEncoding(KGlobal::locale()->language())      + '&' +
                 "window="   + QUrl::toPercentEncoding(m_windowPath)                       + '&' +
                 "context="  + QUrl::toPercentEncoding(m_context)                          + '&' +
                 "comment="  + QUrl::toPercentEncoding(m_comment->toPlainText())           + '&' +
                 "email="    + QUrl::toPercentEncoding(emailAddress));

    kDebug(likeBackDebugArea()) << "http://" << m_likeBack->hostName() << ":" << m_likeBack->hostPort() << m_likeBack->remotePath();
    kDebug(likeBackDebugArea()) << data;

    // Create the HTTP sending object and the actual request
    KUrl url;
    url.setProtocol("http");
    url.setHost(m_likeBack->hostName());
    url.setPort(m_likeBack->hostPort());
    url.setPath(m_likeBack->remotePath());
    KIO::StoredTransferJob *job = KIO::storedHttpPost(data.toUtf8(), url, KIO::HideProgressInfo);
    connect(job, SIGNAL(finished(KJob*)),
            this, SLOT(finished(KJob*)) );
    job->addMetaData("content-type", "Content-Type: application/x-www-form-urlencoded");
}

void LikeBackDialog::finished(KJob *j)
{
    KIO::StoredTransferJob *job = static_cast<KIO::StoredTransferJob*>(j);

    kDebug(likeBackDebugArea()) << "Request has" << (job->error()?"failed":"succeeded");

    m_likeBack->disableBar();

    if (job->error() == 0) {
        KMessageBox::information(this,
                                 i18nc("Dialog box text",
                                       "<p>Your comment has been sent successfully.</p>"
                                       "<p>Thank you for your time.</p>"),
                                 i18nc("Dialog box title", "Comment Sent"));

        hide();
        m_likeBack->enableBar();
        KDialog::accept();
        return;
    }

    // TODO: Save to file if error (connection not present at the moment)
    KMessageBox::error(this,
                       i18nc("Dialog box text",
                             "<p>There has been an error while trying to send the comment.</p>"
                             "<p>Please, try again later.</p>"),
                       i18nc("Dialog box title", "Comment Sending Error"));

    kError(likeBackDebugArea()) << job->error() << ": "<< job->errorText()<<job->errorString();
    m_likeBack->enableBar();

    m_comment->setEnabled(true);
    verify();
}

#include "likebackdialog.moc"
