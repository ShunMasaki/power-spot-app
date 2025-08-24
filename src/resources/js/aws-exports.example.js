// WARNING: This is a dummy config file for AWS Amplify.
// Copy to aws-exports.js and replace all DUMMY values with your actual Cognito settings.

const awsconfig = {
    Auth: {
      Cognito: {
        userPoolId: 'REGION_DUMMYPOOLID',             // 例: ap-northeast-1_XXXXXXX
        userPoolClientId: 'DUMMYCLIENTID1234567890',  // 例: 2lev0bbr4un1so7aipuphl0o9o
        loginWith: {
          oauth: {
            domain: 'DUMMYDOMAIN.auth.REGION.amazoncognito.com',
            scopes: ['email', 'openid'],
            redirectSignIn: ['http://localhost:8080'],
            redirectSignOut: ['http://localhost:8080'],
            responseType: 'code'
          }
        }
      }
    }
  };

  export default awsconfig;
